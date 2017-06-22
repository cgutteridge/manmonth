<div class="mm-graph">
    <div class="mm-target-bar">
        @if( $total == $target )
            <div class="mm-target mm-target-alloc" style="width: {{ 99*$total*$scale }}%">
                <div class="mm-target-inner">{{ $total }} {{$units}} allocated</div>
            </div>
        @endif
        @if( $target > $total )
            @if( $total > 0 )
                <div class="mm-target mm-target-alloc" style="width: {{ 99*$total*$scale }}%">
                    <div class="mm-target-inner">{{ $total }} {{$units}} allocated</div>
                </div>
            @endif
            @if( $showFree )
                <div class="mm-target mm-target-free" style="width: {{ 99*($target-$total)*$scale}}%">
                    <div class="mm-target-inner">{{ $target-$total }} {{$units}} free</div>
                </div>
            @endif
        @endif
        @if( $target < $total )
            <div class="mm-target mm-target-alloc" style="width: {{ 99*$target*$scale }}%">
                <div class="mm-target-inner">{{ $target }} {{$units}} meets target</div>
            </div>
            <div class="mm-target mm-target-over" style="width: {{ 99*($total-$target)*$scale }}%">
                <div class="mm-target-inner">{{ $total-$target }} {{$units}} overload</div>
            </div>
        @endif
    </div>
    <div class="mm-loading-bar">
        @if($loadings)
            @foreach($loadings as $loading )
                @include( 'reportType.recordGraphItem', [
                "opts"=>array_key_exists($loading['category'],$categories)
                ? $categories[$loading['category']]
                : [] ])
            @endforeach
        @endif

        @if( $target > $total && $showFree )
            <div class="mm-hover">
                <div class="mm-loading mm-loading-free"
                     style="width: {{ (99*($target-$total)*$scale) }}%">
                    <div class="mm-loading-inner"></div>
                </div>
            </div>
        @endif
    </div>
    @if( $showTarget )
        <div class="mm-target-indicator-bar">
            <div class="mm-target-indicator" style="width: {{ 99*$target*$scale }}%">
                Target {{ $target }} {{$units}}
            </div>
        </div>
    @endif
</div>

