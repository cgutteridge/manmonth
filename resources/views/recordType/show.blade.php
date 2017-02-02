@extends('page')

@section('title')
    View Record Type: @title($recordType)
@endsection
@section( 'content' )

    <h3>Core information</h3>
    @can('edit',$recordType)
        <p>
            <a type="button" class="btn btn-primary" href="@url($recordType,'edit')">Edit Schema</a>
        </p>
    @endcan

    <h3>Fields</h3>
    @include('showField.list',$meta)

    @foreach( $fields as $field)
        <h4>{{ $field["title"] }}</h4>
        @include('showField.list',$field)
    @endforeach


    <h3>Links from @title($recordType)</h3>
    @if( count($recordType->forwardLinkTypes) )
        <ul>
            @foreach( $recordType->forwardLinkTypes as $linkType )
                <li>
                    @include("cardinality",["min"=>$linkType->domain_min,"max"=>$linkType->domain_max])
                    @link($linkType) relation linking to @link($linkType->range)
                </li>
            @endforeach
        </ul>
    @else
        <p>None</p>
    @endif

    <h3>Links to @title($recordType)</h3>
    @if( count($recordType->backLinkTypes) )
        <ul>
            @foreach( $recordType->backLinkTypes as $linkType )
                <li>
                    @include("cardinality",["min"=>$linkType->range_min,"max"=>$linkType->range_max])
                    <a href="@url($linkType)">@title($linkType,"inverse")</a> relation linking
                    from @link($linkType->domain)
                </li>
            @endforeach
        </ul>
    @else
        <p>None</p>
    @endif

    <h3>Report Types</h3>
    @if( count($recordType->reportTypes ))
        <ul>
            @foreach( $recordType->reportTypes as $reportType )
                <li>
                    @link($reportType) (runs
                    on @link($reportType->baseRecordType()), {{$reportType->rules()->count()}} rule(s))
                </li>
            @endforeach
        </ul>
    @else
        None.
    @endif

@endsection


