@inject("titleMaker","App\Http\TitleMaker")
<table class="mm-record-data">
    <tr>
        <th>Code</th>
        <td>{{$values["name"]}}</td>
    </tr>
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