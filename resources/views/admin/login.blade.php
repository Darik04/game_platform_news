@extends('layouts.base')
@section('title')
Login page
@endsection

@section('content')
<div class="center-wrapper">
    <form class="form" action="" method="POST">
    @csrf
        @if($errors->has('login'))
            <h2 style="color: red;">
                {{$errors->first('login')}}
            </h2>
        @endif
        <h1>Login page</h1>
        <input type="text" placeholder="Username" required name="username">
        <input type="password" placeholder="Password" required name="password">
    
        <button class="button" style="align-self: end;" type="submit">Login</button>
    </form>
</div>
@endsection