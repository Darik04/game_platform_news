@extends('layouts.admin')
@section('title')
Manage Games
@endsection
@section('main')
<table>
    <table width="100%">
        <tr>
            <th>Author</th>
            <th>Title</th>
            <th>Description</th>
            <th>Scores count</th>
            <th>Created at</th>
        </tr>
        @foreach($games as $game)
        <tr>
            <td>{{$game->author}}</td>
            <td>{{$game->title}}</td>
            <td><div style="max-width: 300px; margin: auto;">{{$game->description}}</div></td>
            <td>{{$game->scoreCount}}</td>
            <td>{{$game->created_at}}</td>
            
        </tr>
        @endforeach
    </table>
</table>
@endsection