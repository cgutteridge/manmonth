<?php

namespace App\Models;

use App\Exceptions\DataStructValidationException;
use App\Fields\Field;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * @property string name
 * @property array data
 * @property DocumentRevision documentRevision
 * @property Collection forwardLinkTypes
 * @property Collection backLinkTypes
 * @property Collection records
 */
class RecordType extends DocumentPart
{
    /**
     * @return LinkType[]
     */
    public function forwardLinkTypes()
    {
        return $this->documentRevision->linkTypes()
            ->where( "domain_sid", $this->sid );
    }

    /**
     * @return LinkType[]
     */
    public function backLinkTypes()
    {
        return $this->documentRevision->linkTypes()
            ->where( "range_sid", $this->sid );
    }

    /**
     * @return Record[]
     */
    public function records()
    {
        return $this->documentRevision->records()
            ->where( "record_type_sid", $this->sid );
    }

    // TODO: passing in secondary records could be helpful later
    /**
     * Data to create the record. Should supply data and all 1:n and n:1 links.
     * may supply other links but this is not requred.
     * 1:1 links are not yet supported.
     * @param array $data
     * @param array $forwardLinks
     * @param array $backLinks
     * @return Record
     */
    public function createRecord($data=[], $forwardLinks=[], $backLinks=[])
    {
        // make any single link targets into a list before validation
        foreach( $forwardLinks as $key=>&$value ) {
            if( !is_array( $value ) ) { $value = [$value]; }
        }
        foreach( $backLinks as $key=>&$value ) {
            if( !is_array( $value ) ) { $value = [$value]; }
        }

        // these need to be checked before we create the record
        // there is a good argument for making this validation much
        // smarter and looking and both existing and new links
        $this->validateRecordForwardLinks( $forwardLinks );
        $this->validateRecordBackLinks( $backLinks );

        $record = new Record();
        $record->data = $data;
        $record->documentRevision()->associate( $this->documentRevision );
        $record->record_type_sid = $this->sid;
        $record->validateData();
        $record->save();

        // we've been through validation so assume this is all OK
        foreach( $this->forwardLinkTypes as $linkType ) {
            $targets = @$forwardLinks[$linkType->name];
            if( $targets ) {
                foreach( $targets as $target ) {
                    $linkType->createLink( $record, $target );
                }
            }
        }
        foreach( $this->backLinkTypes as $linkType ) {
            $targets = @$backLinks[$linkType->name];
            if( $targets ) {
                foreach( $targets as $target ) {
                    $linkType->createLink( $target, $record );
                }
            }
        }

        return $record;
    }

    /**
     * @var Field[]
     */
    var $fieldsCache;

    /**
     * @return Field[]
     */
    public function fields() {
        if( !$this->fieldsCache ) { 
            $this->fieldsCache = [];
            foreach( $this->data["fields"] as $fieldData ) {
                $this->fieldsCache []= Field::createFromData( $fieldData );
            }
        }  
        return $this->fieldsCache;
    }

    /**
     * @param string $name
     * @return Field|null
     */
    public function field( $name ) {
        foreach( $this->fields() as $field ) {
            if( $field->data["name"] == $name ) {
                return $field;
            }
        }
        return null; // no such field
    }


    /**
     * Validate forward links to be added to a record of this type
     *  must be relevant links and a legal number
     *  $links are of the format [ link_name=>[ $record,... ]]
     * @param Record[][] $links
     * @throws DataStructValidationException
     */
    public function validateRecordForwardLinks($links ) {
        $linkTypes = $this->forwardLinkTypes;
        $unknownLinks = $links; // we'll reduce this list to actually unknown items
        $issues = [];
        foreach( $linkTypes as $linkType ) {
            // check domain restrictions
            if( @$linkType->dataCache["domain_min"] 
             && count(@$links[$linkType->name]) < $linkType->dataCache["domain_min"] ) {
                $issues []= "Expected minimum of ".$linkType->dataCache["domain_min"]." forward links of type ".$linkType["name"] ;
            }
            if( @$linkType->dataCache["domain_max"] 
             && count(@$links[$linkType->name]) > $linkType->dataCache["domain_max"] ) {
                $issues []= "Expected maximum of ".$linkType->dataCache["domain_max"]." forward links of type ".$linkType["name"] ;
            }
            // check target object(s) are correct type 
            if( @$links[$linkType->name] ) { 
                foreach( $links[$linkType->name] as $record ) {
                    $linkType->validateLinkObject($record);
                    // TODO check $record can accept this additional incoming link
                }
            }

            unset( $unknownLinks[$linkType->name] );
        }
        if( count($unknownLinks) ) {
            foreach( $unknownLinks as $linkName=>$record ) {
                $issues []= "Attempt to add an invalid link type: $linkName";
            }
        } 
        if( count($issues ) ) {
            throw new DataStructValidationException( "Validation fail in recordtype.forwardLinks: ".join( ", ", $issues ));
        }
    }

    /**
     * Validate back links to be added to a record of this type
     *  must be relevant links and a legal number
     *  $links are of the format [ link_name=>[ $record,... ]]
     * @param $links
     * @throws DataStructValidationException
     */
    public function validateRecordBackLinks($links ) {
        $linkTypes = $this->backLinkTypes;
        $unknownLinks = $links; // we'll reduce this list to actually unknown items
        $issues = [];
        foreach( $linkTypes as $linkType ) {
            // check range restrictions
            if( @$linkType->dataCache["range_min"] 
             && count(@$links[$linkType->name]) < $linkType->dataCache["range_min"] ) {
                $issues []="Expected minimum of ".$linkType->dataCache["range_min"]." back links of type ".$linkType["name"] ;
            }
            if( @$linkType->dataCache["range_max"] 
             && count(@$links[$linkType->name]) > $linkType->dataCache["range_max"] ) {
                $issues []="Expected maximum of ".$linkType->dataCache["range_max"]." back links of type ".$linkType["name"] ;
            }
            // check target subject(s) are correct type 
            if( @$links[$linkType->name] ) { 
                foreach( $links[$linkType->name] as $record ) {
                    $linkType->validateLinkSubject($record);
                    // TODO check $record can accept this additional incoming link
                }
            }
            unset( $unknownLinks[$linkType->name] );
        }
        if( count($unknownLinks) ) {
            foreach( $unknownLinks as $linkName=>$record ) {
                $issues []= "Attempt to add an invalid link type: $linkName";
            }
        } 
        if( count($issues ) ) {
            throw new DataStructValidationException( "Validation fail in recordtype.backLinks: ".join( ", ", $issues ));
        }
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateName() {

        $validator = Validator::make(
        [ 'name' => $this->name ],
        [ 'name' => 'required|alpha_dash|min:2|max:255' ]);

        if($validator->fails()) {
            throw new DataStructValidationException( "RecordType", "name", $this->name, $validator->errors() );
        }
    }

    /**
     * @throws DataStructValidationException
     */
    public function validateData() {

        $validator = Validator::make(
          $this->data,
          [ 'fields' => 'required|array', 
            'fields.*.type' => 'required|in:boolean,integer,decimal,string' ]);

        if($validator->fails()) {
            throw new DataStructValidationException( "RecordType", "data", $this->data, $validator->errors() );
        }
        foreach( $this->fields() as $field ) {
            $field->validate();
        }

    }

}


