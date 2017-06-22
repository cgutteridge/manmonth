<tr class="mm-record-report">
    <td class="mm-record-report-icon">
        <a href="@url($record)"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>
    </td>
    <td class="mm-record-report-icon">
        @can('edit',$record)
            <a href="@url($record,'edit')"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a>
        @endcan
    </td>
    @foreach( $recordReport->getColumns() as $colName=>$colValue)
        <td class="mm-record-report-data">{{ $colValue }}</td>
    @endforeach
    @if( count( $reportData["categories"])>1)
        @foreach( $reportData["categories"] as $category=>$opts )
            <td class="mm-record-report-data">{{ $categoryTotals[$category] }}</td>
        @endforeach
    @endif
    <td class="mm-record-report-data">{{ $total }}</td>
    <td class="mm-record-report-data">{{ $target }}</td>
    <td class="mm-record-report-visual">
        <div data-mm-report-visual="absolute">
            @include( 'reportType.recordGraph', [
                        "showFree"=>true,
                        "showTarget"=>true,
                        "record"=>$record,
                        "recordReport"=>$recordReport,
                        "loadings"=>$loadings,
                        "units" => $units,
                        "scale" =>
                                max($reportData["maxLoading"], $reportData["maxTarget"]) == 0 ?
                                1 :
                                1 / max($reportData["maxLoading"], $reportData["maxTarget"]),
                        "target" => $recordReport->getLoadingTarget(),
                        "total" => $recordReport->getLoadingTotal(),
                        "categories" => $reportData["categories"]
                        ])
        </div>
        <div data-mm-report-visual="targets">
            {{--Might have an issue if there's no target?--}}
            @include( 'reportType.recordGraph', [
                       "showFree"=>true,
                       "showTarget"=>true,
                       "record"=>$record,
                       "recordReport"=>$recordReport,
                       "loadings"=>$loadings,
                       "units" => $units,
                       "scale" =>
                                max($reportData["maxLoading"], $reportData["maxTarget"]) == 0 ?
                                1 :
                                ($reportData["maxTarget"]/$recordReport->getLoadingTarget()) / ($reportData["maxRatio"]*max($reportData["maxLoading"], $reportData["maxTarget"])),

                       "target" => $recordReport->getLoadingTarget(),
                       "total" => $recordReport->getLoadingTotal(),
                       "categories" => $reportData["categories"]
                       ])
        </div>
        <div data-mm-report-visual="totals">
            @include( 'reportType.recordGraph', [
                "showFree"=>false,
                "showTarget"=>false,
                "record"=>$record,
                "recordReport"=>$recordReport,
                "loadings"=>$loadings,
                "units" => $units,
                "scale" => $recordReport->getLoadingTotal()==0 ? 1 : 1/$recordReport->getLoadingTotal(),
                "target" => $recordReport->getLoadingTarget(),
                "total" => $recordReport->getLoadingTotal(),
                "categories" => $reportData["categories"]
                ])
        </div>
        <div data-mm-report-visual="breakdown">
            <table class="mm-record-report-visual-breakdown">
                @foreach( $loadings as $loading )
                    <tr>
                        <td>{{$loading["load"]}}</td>
                        <td>{{$loading["category"]}}</td>
                        <td>{{$loading["description"]}}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div data-mm-report-visual="none">
        </div>
    </td>
</tr>