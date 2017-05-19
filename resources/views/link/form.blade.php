<div>
    @include("record.field",[
                "idPrefix"=>$idPrefix."subject",
                "recordType"=>$link->linkType->domain(),
                "record"=>$link->subjectRecord
            ])
    <span style="padding:0 2em">@title($link->linkType)</span>
    @include("record.field",[
                "idPrefix"=>$idPrefix."object",
                "recordType"=>$link->linkType->range(),
                "record"=>$link->objectRecord
            ])
</div>
