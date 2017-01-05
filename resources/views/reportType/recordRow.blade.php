<tr class="mm_record_report">
    <td class="mm_record_report_icon">
        <a href="@url($record)"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>
    </td>
    <td class="mm_record_report_icon">
        @can('edit',$record)
            <a href="@url($record,'edit')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
        @endcan
    </td>
    @foreach( $recordReport->getColumns() as $colName=>$colValue)
        <td class="mm_record_report_data">{{ $colValue }}</td>
    @endforeach
    @if( count( $reportData["categories"])>1)
        @foreach( $reportData["categories"] as $category )
            <td class="mm_record_report_data">{{ $categoryTotals[$category] }}</td>
        @endforeach
    @endif
    <td class="mm_record_report_data">{{ $total }}</td>
    <td class="mm_record_report_data">{{ $target }}</td>
    <td class="mm_record_report_graph">
        @include('reportType.recordGraph')
    </td>
</tr>