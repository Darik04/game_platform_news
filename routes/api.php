<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GameController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('/v1')->group(function (){
    Route::post('auth/signup', [UserController::class, 'signup']);
    Route::post('auth/signin', [UserController::class, 'signin']);
    Route::get('games', [GameController::class, 'getGames']);
    Route::get('games/{game:slug}', [GameController::class, 'getGame']);
    Route::get('games/{game:slug}/scores', [GameController::class, 'getTopScores']);
    Route::get('game/{game:slug}/{path}', [GameController::class, 'getGamePath']);
    Route::post('games/{game:slug}/upload', [GameController::class, 'uploadVersion']);

    Route::middleware('auth:sanctum')->group(function () {
    
        Route::post('games/{game:slug}/scores', [GameController::class, 'setScore']);
        Route::get('users/{user:username}', [UserController::class, 'getUser']);
        Route::put('games/{game:slug}', [GameController::class, 'updateGame']);
        Route::post('auth/signout', [UserController::class, 'signout']);
        Route::post('games', [GameController::class, 'createGame']);
    }); 
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
