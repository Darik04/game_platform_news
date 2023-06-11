@extends('layouts.admin')
@section('title')
Admin users
@endsection
@section('main')
<table>
    <table width="100%">
        <tr>
            <th>Username</th>
            <th>Registered</th>
            <th>Last login</th>
        </tr>
        @foreach($users as $user)
        <tr>
            <td>{{$user->username}}</td>
            <td>{{$user->created_at}}</td>
            <td>{{$user->last_login_at}}</td>
        </tr>
        @endforeach
    </table>
</table>
@endsection