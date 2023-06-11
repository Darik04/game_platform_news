<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admin', [AdminController::class, 'index'])->name('login');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('logout');
Route::post('/admin', [AdminController::class, 'login']);


Route::get('/', function () {
    if(!Auth::check()){
        return redirect('/admin');
    }
    return redirect('/admin/user');
});

Route::middleware('auth:web')->group(function (){
    Route::get('/admin/user', [AdminController::class, 'adminUsers']);
    Route::get('/users/user', [AdminController::class, 'platformUsers']);
    Route::get('/games/games', [AdminController::class, 'games']);
    Route::post('/user/{user:id}/lock', [AdminController::class, 'lockUser']);
    Route::post('/user/{user:id}/unlock', [AdminController::class, 'unlockUser'])->withTrashed();
});
