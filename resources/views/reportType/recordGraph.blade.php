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
    @if($loadings)
        @foreach($loadings as $loading )
            @include( 'reportType.recordGraphItem', [
            "opts"=>array_key_exists($loading['category'],$reportData['categories'])
            ? $reportData['categories'][$loading['category']]
            : [] ])
        @endforeach
    @endif

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

