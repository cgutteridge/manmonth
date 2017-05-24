@extends('page')

@section('title')
    View Record Type: @title($recordType)
@endsection
@section( 'content' )
    <p>
        <a type="button" class="btn btn-primary" href="@url($recordType,'records')">
            List records
        </a>
    </p>
    <table class="mm-record">
        <thead>
        <tr style="font-size: 150%">
            <th>Core information</th>
            @can('edit',$recordType)
                <th class="mm-record-action"><a href="@url($recordType,'edit')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></th>
            @endcan
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="2">
                @include('showField.list',$meta)
            </td>
        </tr>
        </tbody>

    </table>

    <h3>Fields</h3>

    @foreach( $fields as $field)
        <table class="mm-record">
            <thead>
            <tr style="font-size: 150%">
                <th>{{ $field["title"] }}</th>
                @can('edit',$recordType)
                    <th class="mm-record-action"><a href="{{$field["edit"]}}"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></th>
                @endcan
            </tr>
            </thead>
            <tbody>
            <tr>
                <td colspan="2">
                    @include('showField.list',$field)
                </td>
            </tr>
            </tbody>
        </table>
    @endforeach


    <h3>Links from @title($recordType)</h3>
    @if( count($recordType->forwardLinkTypes) )
        <ul>
            @foreach( $recordType->forwardLinkTypes as $linkType )
                <li>
                    @include("cardinality",["min"=>$linkType->domain_min,"max"=>$linkType->domain_max])
                    @link($linkType) relation linking to @link($linkType->range())
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
                    from @link($linkType->domain())
                </li>
            @endforeach
        </ul>
    @else
        <p>None</p>
    @endif

    <h3>Report Types</h3>
    @if( count($recordType->reportTypes() ))
        <ul>
            @foreach( $recordType->reportTypes() as $reportType )
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


