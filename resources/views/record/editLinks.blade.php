@inject("titleMaker","App\Http\TitleMaker")
@foreach( $record->recordType->forwardLinkTypes as $linkType )
    @include("editField.link",[
    "title"=>$titleMaker->title($linkType),
    "idPrefix"=>$idPrefix."link_".$linkType->id."_",
    "min"=>$linkType->domain_min,
    "max"=>$linkType->domain_max,
    "records"=>$record->forwardLinkedRecords($linkType),
    "type"=>$linkType->range_type,
    "recordType"=>$linkType->range
])
@endforeach
@foreach( $record->recordType->backLinkTypes as $linkType )
    @include("editField.link",[
    "title"=>$titleMaker->title($linkType,"inverse"),
    "idPrefix"=>$idPrefix."link_".$linkType->id."_",
    "min"=>$linkType->range_min,
    "max"=>$linkType->range_max,
    "records"=>$record->backLinkedRecords($linkType),
    "type"=>$linkType->domain_type,
    "recordType"=>$linkType->domain
])
@endforeach
