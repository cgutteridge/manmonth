<tr class="mm_record_report">
    <td>
        <a href="@url($record)"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>
    </td>
    <td>
        @can('edit',$record)
            <a href="@url($record,'edit')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
        @endcan
    </td>
    @foreach( $recordReport->getColumns() as $colName=>$colValue)
        <td>{{ $colValue }}</td>
    @endforeach
    <td>{{ $total }}</td>
    <td>{{ $target }}</td>
    <td>
        @include('reportType.recordGraph')
    </td>
</tr>