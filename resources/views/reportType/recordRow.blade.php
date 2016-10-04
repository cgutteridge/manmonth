<div class="mm_record_report">

    <div>
        @foreach( $recordReport->getColumns() as $colName=>$colValue)
            <b>{{ $colName }}:</b> {{ $colValue }} |
        @endforeach
        <b>Allocated load:</b> {{ $total }} |
        <b>Target load:</b> {{ $target }}
    </div>

    @if( $showTarget )
        <div class="mm_target_indicator_bar">
            <div class="mm_target_indicator" style="width: {{ 100*$target*$scale }}%">
                Target {{ $target }} hours
            </div>
        </div>
    @endif

    <div class="mm_target_bar">
        @if( $total == $target )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total*$scale }}%">
                <div class="mm_target_inner">{{ $total }} hours allocated</div>
            </div>
        @endif
        @if( $target > $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total*$scale }}%">
                <div class="mm_target_inner">{{ $total }} hours allocated</div>
            </div>
            @if( $showFree )
                <div class="mm_target mm_target_free" style="width: {{ 100*($target-$total)*$scale}}%">
                    <div class="mm_target_inner">{{ $target-$total }} hours free</div>
                </div>
            @endif
        @endif
        @if( $target < $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$target*$scale }}%">
                <div class="mm_target_inner">{{ $target }} hours meets target</div>
            </div>
            <div class="mm_target mm_target_over" style="width: {{ 100*($total-$target)*$scale }}%">
                <div class="mm_target_inner">{{ $total-$target }} hours overload</div>
            </div>
        @endif
    </div>

    <div class="mm_loading_bar">
        @foreach($recordReport->getLoadings() as $loading )
            <div class="mm_loading mm_cat_{{ $loading['category'] }}" style="width: {{ 100*$loading['load']*$scale }}%">
                <div class="mm_loading_inner">
                    {{ $loading['load']}} hours<br>{{ $loading['description'] }}
                </div>
                <div class="hover">
                    <div class="mm_loading_hover mm_cat_{{ $loading['category'] }}">
                        {{ $loading['load']}} hours<br>{{ $loading['description'] }}
                    </div>
                </div>
            </div>
        @endforeach
        @if( $target > $total && $showFree )
            <div class="mm_loading mm_loading_free"
                 style="width: {{ (100*($target-$total)*$scale) }}%">
                <div class="mm_loading_inner"></div>
            </div>
        @endif
    </div>
    @if( $showTarget )
        <div class="mm_target_indicator_bar">
            <div class="mm_target_indicator" style="width: {{ 100*$target*$scale }}%">
                Target {{ $target }} hours
            </div>
        </div>
    @endif

</div>