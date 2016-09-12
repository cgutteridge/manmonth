<div class="mm_record_report">
<!--
    <div style="border:solid 1px green;padding: 1em; margin:1em;">
        <div>RECORD</div>
        {{ print_r($record,1) }}
    </div>
    <pre style="border:solid 1px green;padding: 1em; margin:1em;">
        <div>report</div>
        {{ print_r($recordReport,1) }}
    </pre>
        -->

        <div>
        @foreach( $recordReport->getColumns() as $colName=>$colValue)
                <b>{{ $colName }}:</b> {{ $colValue }} |
        @endforeach
            <b>Allocated load:</b> {{ $total }} |
            <b>Target load:</b> {{ $target }}
    </div>

    <div class="mm_target_bar">
        @if( $total == $target )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total/$scaleSize }}%" title="{{ $total }} allocated">
                <div class="mm_target_inner">{{ $total }} allocated</div>
            </div>
        @endif
        @if( $target > $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total/$scaleSize }}%" title="{{ $total }} allocated">
                <div class="mm_target_inner">{{ $total }} allocated</div>
            </div>
            <div class="mm_target mm_target_free" style="width: {{ 100*($target-$total)/$scaleSize }}%" title="{{ $target-$total }} free">
                <div class="mm_target_inner">{{ $target-$total }} free</div>
            </div>
        @endif
        @if( $target < $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$target/$scaleSize }}%" title="{{ $target }} meets target">
                <div class="mm_target_inner">{{ $target }} meets target</div>
            </div>
            <div class="mm_target mm_target_over" style="width: {{ 100*($total-$target)/$scaleSize }}%" title="{{ $total-$target }} overload">
                <div class="mm_target_inner">{{ $total-$target }} overload</div>
            </div>
        @endif
    </div>

    <div class="mm_loading_bar">
        @foreach($recordReport->getLoadings() as $loading )
        <div class="mm_loading mm_cat_{{ $loading['category'] }}" style="width: {{ 100*$loading['load']/$scaleSize }}%" title="{{$loading['load']}} : {{ $loading['description'] }}">
            <div class="mm_loading_inner" >
                {{ $loading['load']}}<br>{{ $loading['description'] }}
            </div>
        </div>
        @endforeach
        @if( $target > $total )
            <div class="mm_loading" style="width: {{ 100*($target-$total)/$scaleSize }}%" >
                <div class="mm_loading_inner">Unallocated</div>
            </div>
        @endif
    </div>

</div>