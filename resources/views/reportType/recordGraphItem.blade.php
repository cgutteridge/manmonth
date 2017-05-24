<div class="mm-hover">
    <div class="mm_hover_target mm-loading"
         style="width: {{ 100*$loading['load']*$scale }}%;
         {{array_key_exists('background_color',$opts)?"background-color: ".$opts['background_color'].";":""}}
                 "

    >
        <a
                @if(array_key_exists("record_id",$loading))
                href="@url( \App\Models\Record::find($loading["record_id"]) )"
                @endif
                style="{{array_key_exists('text_color',$opts)?"color: ".$opts['text_color'].";":""}}"
                class="mm-loading-inner">
            {{ $loading['description'] }} - {{ $loading['load']}} {{$units}}
        </a>
    </div>
    <div class="mm-hover-message">
        <div class="mm-loading-hover"
             style="
             {{array_key_exists('background_color',$opts)?"background-color: ".$opts['background_color'].";":""}}
             {{array_key_exists('text_color',$opts)?"color: ".$opts['text_color'].";":""}}
                     "
        >
            @if( !empty($loading['category']) )
                <div class="mm-loading-hover-category">Category
                    "{{array_key_exists('label',$opts)?$opts['label']:$loading['category']}}"
                </div>
            @endif
            <div class="mm-loading-hover-description">{{ $loading['description'] }}
                - {{ $loading['load']}} {{$units}}.
            </div>
            <div class="mm-loading-hover-rule">From rule "{{$loading['rule_title']}}"</div>
        </div>
    </div>
</div>
