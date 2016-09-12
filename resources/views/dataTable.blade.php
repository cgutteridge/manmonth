<table class="mm_datatable">
    @foreach( $data as $key=>$value )
        <tr>
            <th>{{$key}}:</th>
            <td>

            @if( is_array( $value ))
                @include( "dataTable", [ "data"=>$value ] )
            @else
                {{ $value }}
            @endif
            </td>
        </tr>
    @endforeach
</table>
