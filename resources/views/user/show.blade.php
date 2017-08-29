@extends('page')

@section('title')
    View User: @title($user)
@endsection

@section( 'content' )

    @if( isset($roles) )
        <h2>Roles</h2>
        <ul>
            @foreach( $roles as $subject )
                <li>
                    <strong>
                        @if( isset($subject["document"]) )
                            <a href="{{$subject["document"]["url"]}}">{{$subject["document"]["title"]}}</a>
                        @else
                            General permissions
                        @endif
                    </strong>
                    <ul>
                        <li>Roles
                            <ul>
                                @foreach( $subject["roles"] as $role )
                                    <li>{{$role["title"]}}</li>
                                @endforeach
                            </ul>
                        </li>
                        <li>Permissions
                            <ul>
                                @foreach( $subject["permissions"] as $permission )
                                    <li>{{$permission["title"]}} [<code>{{$permission["name"]}}</code>]</li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </li>
            @endforeach
        </ul>
    @endif

@endsection


