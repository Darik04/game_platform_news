<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Administrator;
use App\Models\User;
use App\Models\Game;

class AdminController extends Controller
{
    public function index(Request $request){
        if (Auth::check()) {
            return redirect('/');
        }
        return view('admin.login');
    }


    public function login(Request $request){

        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $cred = $request->only('username', 'password');
        error_log('ERR: '.$request);
        if (Auth::attempt($cred)) {
            $admin = Administrator::find(Auth::user()->id);
            $admin->last_login_at = now();
            $admin->save();
            return redirect('/');
        }
        return redirect('/admin')->withErrors(['login' => 'Invalid credentials']);
    }


    public function logout(Request $request){
        Auth::logout();
        return redirect('/admin');
    }



    public function adminUsers(Request $request){
        
        return view('admin.users', ['users' => Administrator::all()]);
    }
    public function platformUsers(Request $request){
        
        return view('users.users', ['users' => User::withTrashed()->get()]);
    }

    public function lockUser(User $user, Request $request){
        $user->delete_reason = $request->get('reason');
        $user->save();
        $user->delete();
        return redirect()->back();
    }
    public function unlockUser(User $user, Request $request){
        $user->restore();
        $user->delete_reason = null;
        $user->save();
        return redirect()->back();
    }




    public function games(Request $request){
        error_log(Game::withTrashed()->get());
        return view('games.games', ['games' => Game::withTrashed()->get()]);
    }
}
