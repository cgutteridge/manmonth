<tr id="{{$idPrefix}}inline_edit" data-mm-dynamic="inline-link-edit">
    <th>
        @include("cardinality",["min"=>$min,"max"=>$max])
        {{$title}}@if( $min>0 ) (required)@endif:
    </th>
    <td>
        @if( !isset($type) )
            <ul id="{{$idPrefix}}list" class="mm-link-edit-list">
                @foreach( $records as $linkedRecord)
                    <li
                            class="mm-link-edit-list-existing"
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
        @else
            This is a complex relationship where links require extra information. Such links can be modified
            via the main record page. This mesage will be removed before v1.0
        @endif
    </td>
</tr>
