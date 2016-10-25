<tr id="{{$idPrefix}}inline_edit" data-mm-dynamic="inline-link-edit">
    <th>
        {{$title}}
        @include("cardinality",["min"=>$min,"max"=>$max])
        @if( $min>0 )
            (required)
        @endif
    </th>
    <td>
        <ul id="{{$idPrefix}}list" class="mm-link-edit-list">
            @foreach( $records as $linkedRecord)
                <li class="mm-link-edit-list-existing">
                    <a
                            data-rid="{{$linkedRecord->id}}"
                            class="mm-record-stub mm-record-entity mm-record-{{$linkedRecord->id}}"
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
                    <input class="mm-form-action" name="{{$idPrefix}}remove_{{$linkedRecord->id}}" value="0"/>
                </li>
            @endforeach
            @if( !isset($type) )
                <li class="mm-link-edit-list-add">
                    @include("record.field",[
                        "idPrefix"=>$idPrefix."add",
                        "recordType"=>$recordType
                    ])
                    <a class="mm-button mm-button-add">
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                        ADD LINK
                    </a>
                </li>
            @else
                <li>This is a complex relationship where links require extra information. Such links can be created via
                    the record page.
                </li>
            @endif
        </ul>
    </td>
</tr>