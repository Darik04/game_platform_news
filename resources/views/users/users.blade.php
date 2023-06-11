@extends('layouts.admin')
@section('title')
Platform users
@endsection
@section('main')
<table>
    <table width="100%">
        <tr>
            <th>Username</th>
            <th>Registered</th>
            <th>Last login</th>
            <th>Blocked</th>
        </tr>
        @foreach($users as $user)
        <tr>
            <td>{{$user->username}}</td>
            <td>{{$user->created_at}}</td>
            <td>{{$user->last_login_at}}</td>
            <td>
                @if($user->trashed())
                <form action="{{ url('/user/'.$user->id.'/unlock') }}" method="post">
                    @csrf
                    <button class="button">Unblock</button>
                </form>
                @else
                <form action="{{ url('/user/'.$user->id.'/lock') }}" method="post">
                    @csrf
                    <button class="button" name="reason" type="submit" value="spamming">Spamming</button>
                    <button class="button" name="reason" type="submit" value="cheating">Cheating</button>
                    <button class="button" name="reason" type="submit" value="other">Other</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</table>
@endsection