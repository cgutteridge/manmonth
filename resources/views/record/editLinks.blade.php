@inject("titleMaker","App\Http\TitleMaker")
@foreach( $record->recordType->forwardLinkTypes as $linkType )
    @include("editField.link",[
    "title"=>$titleMaker->title($linkType),
    "idPrefix"=>$idPrefix."fwd_".$linkType->sid."_",
    "min"=>$linkType->domain_min,
    "max"=>$linkType->domain_max,
    "records"=>$record->forwardLinkedRecords($linkType),
    "type"=>$linkType->range_type,
    "recordType"=>$linkType->range,
    "linkChanges"=>$linkChanges["fwd"]
])
@endforeach
@foreach( $record->recordType->backLinkTypes as $linkType )
    @include("editField.link",[
    "title"=>$titleMaker->title($linkType,"inverse"),
    "idPrefix"=>$idPrefix."bck_".$linkType->sid."_",
    "min"=>$linkType->range_min,
    "max"=>$linkType->range_max,
    "records"=>$record->backLinkedRecords($linkType),
    "type"=>$linkType->domain_type,
    "recordType"=>$linkType->domain,
    "linkChanges"=>$linkChanges["bck"]
])
@endforeach
