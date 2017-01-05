<div class="mm_hover">
    <div class="mm_hover_target mm_loading"
         style="width: {{ 100*$loading['load']*$scale }}%;
         {{array_key_exists('background_color',$opts)?"background-color: ".$opts['background_color'].";":""}}
                 "

    >
        <a
                @if(array_key_exists("record_id",$loading))
                href="@url( \App\Models\Record::find($loading["record_id"]) )"
                @endif
                style="{{array_key_exists('text_color',$opts)?"color: ".$opts['text_color'].";":""}}"
                class="mm_loading_inner">
            {{ $loading['description'] }} - {{ $loading['load']}} {{$units}}
        </a>
    </div>
    <div class="mm_hover_message">
        <div class="mm_loading_hover"
             style="
             {{array_key_exists('background_color',$opts)?"background-color: ".$opts['background_color'].";":""}}
             {{array_key_exists('text_color',$opts)?"color: ".$opts['text_color'].";":""}}
                     "
        >
            @if( !empty($loading['category']) )
                <div class="mm_loading_hover_category">Category
                    "{{array_key_exists('label',$opts)?$opts['label']:$loading['category']}}"
                </div>
            @endif
            <div class="mm_loading_hover_description">{{ $loading['description'] }}
                - {{ $loading['load']}} {{$units}}.
            </div>
            <div class="mm_loading_hover_rule">From rule "{{$loading['rule_title']}}"</div>
        </div>
    </div>
</div>
