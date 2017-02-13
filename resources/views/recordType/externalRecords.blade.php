@extends('page')

@section('title')
    View Records of Type @title($recordType)
@endsection

@section( 'content' )
    <p><a href="{{$importUrl}}" class="btn btn-primary"  onclick="return confirm('WARNING: You are about to import {{$resultsCount}} records. There is no undo.')">Import all matches</a></p>

    <form>
        @if($totalCount>$maxSize)
            <p>
                <input type="submit" class="btn btn-primary" value="Update"/>
                Use % character as a wildcard in filters.
            </p>
        @endif

        <table class="mm-datatable">
            <thead>
            <tr>
                <th></th>
                <th></th>
                @foreach( $columns as $column)
                    <th style="text-align:left">{{$column}}</th>
                @endforeach
            </tr>
            @if($totalCount>$maxSize)
                <tr>
                    <th></th>
                    <th></th>
                    @foreach( $columns as $column)
                        <th style="text-align:left"><input name="filter_{{$column}}" size="8" placeholder="filter"
                             @if( array_key_exists($column,$filters))value="{{$filters[$column]}}"@endif
                            />
                        </th>
                    @endforeach
                </tr>
            @endif
            </thead>
            <tbody>
            @foreach( $rows as $row )
                <tr>
                    @if( isset($row->_record))
                        <td class="mm_record_report_icon">
                            <a href="@url($row->_record)"><span class="glyphicon glyphicon-eye-open"
                                                                aria-hidden="true"></span></a>
                        </td>
                        <td></td>
                    @else
                        <td></td>
                        <td class="mm_record_report_icon">
                            @can('create',$recordType)
                                <a href="{{$row->_create}}"><span class="glyphicon glyphicon-plus-sign"
                                                                  aria-hidden="true"></span></a>
                            @endcan
                        </td>
                    @endif

                    @foreach( $columns as $column)
                        <td>{{$row->$column}}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
        @if( $resultsCount>$maxSize )
            <p>+{{$resultsCount-$maxSize}} additional record{{$resultsCount-$maxSize==1?"":"s"}}. You should use the
                filters option in
                the top of the table.</p>
        @endif
    </form>
@endsection


