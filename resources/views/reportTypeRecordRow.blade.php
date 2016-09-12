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

    @if( $showTarget )
        <div class="mm_target_bar">
            <div class="mm_target_indicator" style="width: {{ 100*$target*$scale }}%">
                Target {{ $target }}
            </div>
        </div>
    @endif

    <div class="mm_target_bar">
        @if( $total == $target )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total*$scale }}%" title="{{ $total }} allocated">
                <div class="mm_target_inner">{{ $total }} allocated</div>
            </div>
        @endif
        @if( $target > $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$total*$scale }}%" title="{{ $total }} allocated">
                <div class="mm_target_inner">{{ $total }} allocated</div>
            </div>
            @if( $showFree )
            <div class="mm_target mm_target_free" style="width: {{ 100*($target-$total)*$scale}}%" title="{{ $target-$total }} free">
                <div class="mm_target_inner">{{ $target-$total }} free</div>
            </div>
            @endif
        @endif
        @if( $target < $total )
            <div class="mm_target mm_target_alloc" style="width: {{ 100*$target*$scale }}%" title="{{ $target }} meets target">
                <div class="mm_target_inner">{{ $target }} meets target</div>
            </div>
            <div class="mm_target mm_target_over" style="width: {{ 100*($total-$target)*$scale }}%" title="{{ $total-$target }} overload">
                <div class="mm_target_inner">{{ $total-$target }} overload</div>
            </div>
        @endif
    </div>

    <div class="mm_loading_bar">
        @foreach($recordReport->getLoadings() as $loading )
        <div class="mm_loading mm_cat_{{ $loading['category'] }}" style="width: {{ 100*$loading['load']*$scale }}%" title="{{$loading['load']}} : {{ $loading['description'] }}">
            <div class="mm_loading_inner" >
                {{ $loading['load']}}<br>{{ $loading['description'] }}
            </div>
        </div>
        @endforeach
        @if( $target > $total && $showFree )
            <div class="mm_loading" style="background-image: url(/stripe.png); width: {{ 100*($target-$total)*$scale }}%" >
                <div class="mm_loading_inner">&nbsp;</div>
            </div>
        @endif
    </div>
    @if( $showTarget )
    <div class="mm_target_bar">
        <div class="mm_target_indicator" style="width: {{ 100*$target*$scale }}%">
            Target {{ $target }}
        </div>
    </div>
    @endif

</div>