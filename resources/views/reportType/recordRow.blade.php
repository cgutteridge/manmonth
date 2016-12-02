<div class="mm_record_report">

    <div>
        @foreach( $recordReport->getColumns() as $colName=>$colValue)
            <b>{{ $colName }}:</b> {{ $colValue }} |
        @endforeach
        <b>Category:</b> {{ $loadingType }} |
        <b>Allocated:</b> {{ $total }} |
        <b>Target:</b> {{ $target }}
    </div>

    @if( $showTarget )
        <div class="mm_target_indicator_bar">
            <div class="mm_target_indicator" style="width: {{ 100*$target*$scale }}%">
                Target {{ $target }} {{$units}}
            </div>
        </div>
    @endif

    <div class="mm_target_bar">
        @if( $total == $target )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total*$scale }}%">
                <div class="mm_target_inner">{{ $total }} {{$units}} allocated</div>
            </div>
        @endif
        @if( $target > $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total*$scale }}%">
                <div class="mm_target_inner">{{ $total }} {{$units}} allocated</div>
            </div>
            @if( $showFree )
                <div class="mm_target mm_target_free" style="width: {{ 100*($target-$total)*$scale}}%">
                    <div class="mm_target_inner">{{ $target-$total }} {{$units}} free</div>
                </div>
            @endif
        @endif
        @if( $target < $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$target*$scale }}%">
                <div class="mm_target_inner">{{ $target }} {{$units}} meets target</div>
            </div>
            <div class="mm_target mm_target_over" style="width: {{ 100*($total-$target)*$scale }}%">
                <div class="mm_target_inner">{{ $total-$target }} {{$units}} overload</div>
            </div>
        @endif
    </div>

    <div class="mm_loading_bar">
        @foreach($loadings as $loading )
            <div class="mm_hover">
                <div class="mm_hover_target mm_loading mm_cat_{{ $loading['category'] }}"
                     style="width: {{ 100*$loading['load']*$scale }}%">
                    <div class="mm_loading_inner">
                        {{ $loading['description'] }} - {{ $loading['load']}} {{$units}}
                    </div>
                </div>
                <div class="mm_hover_message">
                    <div class="mm_loading_hover mm_cat_{{ $loading['category'] }}">
                        {{ $loading['description'] }} - {{ $loading['load']}} {{$units}}
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
                Target {{ $target }} {{$units}}
            </div>
        </div>
    @endif

</div>