<table class="mm-datatable">
    @foreach( $list as $permission )
        <tr>
            <th>{{$permission['name']}}:</th>
            <td>{{$permission['label']}}</td>
        </tr>
    @endforeach
</table>
