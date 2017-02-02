@inject("titleMaker","App\Http\TitleMaker")
<table class="mm-record-data">
    @foreach( $fields as $field)
        @if( isset($values[$field->data["name"]]) )
            <tr>
                <th>{{$titleMaker->title($field)}}</th>
                <td>
                    @include('showField.field',["field"=>$field,"value"=>$values[$field->data["name"]]])
                </td>
            </tr>
        @endif
    @endforeach
</table>