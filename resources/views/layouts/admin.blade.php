@extends('layouts.base')
@section('content')
<header>
    <div class="container head-container">
        <nav>
            <a href="{{ url('/admin/user') }}">Admin Users</a>
            <a href="{{ url('/users/user') }}">Platform Users</a>
            <a href="{{ url('/games/games') }}">Manage Games</a>
        </nav>

        <a class="logout-a" href="{{ url('/admin/logout') }}">Logout</a>
    </div>
</header>

<div class="container">
    <h1 class="main-title">- @yield('title')</h1>
    @yield('main')
</div>
@endsection
