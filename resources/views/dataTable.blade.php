@foreach( $data as $key=>$value )

    <tr>
        <th>{{$key}}:</th>
        <td style="width:100%">

            @if( is_array( $value ))
                <table class="table">
                    @include( "dataTable", [ "data"=>$value ] )
                </table>
            @else
                {{ $value }}
            @endif
        </td>
    </tr>
@endforeach
