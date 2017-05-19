<table
        data-rid="{{$record->sid}}"
        class="mm-record-stub mm-record-entity mm-record-{{$record->sid}}"
>
    <tbody>
    <tr>
        <td class="mm-record-stub-title">
            <a href="@url($record)">
                @title( $record )
            </a>
        </td>

        @can('edit',$record)
            <td class="mm-record-stub-action">
                <a href="@url($record, 'edit', ["_mmreturn"=>@$returnURL])">
                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                </a>
            </td>
        @endcan
    </tr>
    </tbody>
</table>
