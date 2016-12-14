@if( !isset($type) )
    <tr id="{{$idPrefix}}inline_edit"
        data-mm-dynamic="inline-link-edit"
        data-mm-min="{{$min}}"
        @if( isset($max) )
        data-mm-max="{{$max}}"
            @endif
    >
        <th>
            @include("cardinality",["min"=>$min,"max"=>$max])
            {{$title}}@if( $min>0 ) (required)@endif:
        </th>
        <td>
            <ul id="{{$idPrefix}}list"
                class="mm-link-edit-list"
            >
                @foreach( $records as $linkedRecord)
                    <li
                            class="mm-link-edit-list-existing"
                            data-mm-sid="{{$linkedRecord->sid}}"
                            @if( array_key_exists($linkedRecord->sid,$linkChanges["remove"]))
                            data-mm-remove="true"
                            @endif
                    >
                        <a
                                data-rid="{{$linkedRecord->sid}}"
                                class="mm-record-stub mm-record-entity mm-record-{{$linkedRecord->sid}}"
                        >
                            @title( $linkedRecord )
                        </a>

                        <a class="mm-button mm-button-remove">
                            <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                            BREAK LINK
                        </a>
                        <a class="mm-button mm-button-undo">
                            UNDO
                        </a>
                        <input class="mm-form-action" name="{{$idPrefix}}remove_{{$linkedRecord->sid}}" value="0"/>
                    </li>
                @endforeach
                <li class="mm-link-edit-list-add" data-mm-idprefix="{{$idPrefix}}"
                    data-mm-add="{{ join( ",", array_keys($linkChanges["add"])) }}"
                    @foreach($linkChanges["add"] as $sid=>$addTitle )
                    data-mm-add-{{$sid}}="{{$addTitle}}"
                        @endforeach
                >
                @include("record.field",[
                    "idPrefix"=>$idPrefix."add",
                    "recordType"=>$recordType
                ])
                <!--
                    <a class="mm-button mm-button-add">
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                        ADD LINK
                    </a>
                    -->
                </li>
            </ul>
        </td>
    </tr>
@endif