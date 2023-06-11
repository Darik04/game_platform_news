<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Game;
use App\Models\GameVersion;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function signup(Request $request){
        $request->validate([
            'username' => 'required|min:4|max:60|unique:users',
            'password' => 'required|min:8|max:65536'
        ]);

        $new_user = new User();
        $new_user->username = $request->get('username');
        $new_user->password = Hash::make($request->get('password'));
        $new_user->save();


        return response()->json([
            'status' => 'success',
            'token' => $new_user->createToken('api')->plainTextToken
        ], 201);
    }


    public function signin(Request $request){
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->get('username'))->withTrashed()->first();

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Wrong username or password',
            ], 401);
        }

        if ($user->trashed()) {
            return [
                'status' => 'blocked',
                'message' => 'User blocked',
                'reason' => User::$DELETE_REASONS[$user->delete_reason] ?? null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'token' => $user->createToken('api')->plainTextToken,
        ], 200);
    }



    public function signout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
        ], 200);
    }




    public function getUser(Request $request, User $user){

        $games = Game::where('created_by', $user->id)->get();
        $highscores = Score::where('user_id', $user->id)->get();
        $scores = [];
        foreach($games as $game){
            $lastVersion = GameVersion::orderBy('id', 'desc')->where('game_id', $game->id)->first();
            error_log('RES: '.$lastVersion);
            $scoresOfGame = [];
            if($lastVersion){
                $scoresOfGame = Score::where('game_version_id', $lastVersion->id)->get();
            }
            $allScores = 0;

            foreach($scoresOfGame as $scoreOfGame){
                $allScores = $allScores + $scoreOfGame->score;
            }
            $scores[] = [
                'game' => [
                    'slug' => $game->slug,
                    'title' => $game->title,
                    'description' => $game->description,
                ],
                'score' => $allScores,
                'timestamp' => $game->created_at,
            ];
        }

        return response()->json([
            'username' => $user->username,
            'registeredTimestamp' => $user->created_at,
            'authoredGames' => $games->map(function ($game){
                return [
                    'slug' => $game->slug,
                    'title' => $game->title,
                    'description' => $game->description,

                ];
            }),

            'highscores' => $scores
        ], 200);
    }
}
