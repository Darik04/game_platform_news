<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use App\Models\Score;
use App\Models\GameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Laravel\Sanctum\PersonalAccessToken;
use ZipArchive;

class GameController extends Controller
{
    public function getGames(Request $request){

        $sortBy = $request->get('sortBy', 'popular');
        $sortDir = $request->get('sortDir', 'asc');


        $content = [];
        $page = ((int)$request->get('page', 1))-1;
        $size = ((int)$request->get('size', 10));
        $totalElements = Game::count();
        $games = Game::orderBy('id', $sortDir)->get();

        // $sortDir = $request->get('sortDir', 'asc');
        if($sortBy == 'title'){
            $games = Game::orderBy('title', $sortDir)->get();
        }else if($sortBy == 'uploadate'){
            $games = Game::orderBy('uploadate', $sortDir)->get();
        }else if($sortBy == 'popular'){
            $games = Game::orderBy('created_by', $sortDir)->get();
        }


        $content = $games->skip($page*$size)->take($size);
        return response()->json([
            "page" => $page+1,
            "size" => $size,
            "totalElements" => $totalElements,
            "content" => $content->map(function ($game){
                return collect($game)->except(['created_at', 'game_author']);
            })
        ], 200);

    }



    public function createGame(Request $request){
        $request->validate([
            "title" => "required|min:3|max:60",
            "description" => "required|min:0|max:200",
        ]);

        $slug = Str::slug($request->get('title'));

        if(Game::where('slug', $slug)->first()){
            return response()->json([
                "status" => "invalid",
                "slug" => "Game title already exists"
            ], 400);
        }

        $new_game = new Game();
        $new_game->title = $request->get('title');
        $new_game->slug = $slug;
        $new_game->description = $request->get('description');
        $new_game->created_by = $request->user()->id;

        $new_game->save();

        return response()->json([
            "status" => "success",
            "slug" => $slug
        ], 201);
    }



    public function updateGame(Request $request, Game $game){
        $request->validate([
            "title" => "required|min:3|max:60",
            "description" => "required|min:0|max:200",
        ]);

        if($game->created_by !== $request->user()->id){
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the game author"
            ], 403);
        }

        $game->title = $request->get('title');
        $game->description = $request->get('description');
        $game->save();

        return response()->json([
            "status" => "success",
        ], 200);
    }

    public function deleteGame(Request $request, Game $game){
        if($game->created_by !== $request->user()->id){
            return response()->json([
                "status" => "forbidden",
                "message" => "You are not the game author"
            ], 403);
        }
        $game->delete();
        return response()->json([
        ], 204);
    }




    public function getGame(Request $request, Game $game){
        return collect($game)->except(['created_at', 'game_author']);
    }


    public function uploadVersion(Request $request, Game $game){
        $request->validate([
            'zipfile' => 'required|file',
            'token' => 'required',
        ]);

        $token = PersonalAccessToken::findToken($request->get('token'));
        if(!$token){
            return response('Unauthorized', 401);
        }

        if($token->tokenable->id !== $game->created_by){
            return response('A game can be only uploaded by author', 401);
        }
        $version = 'v1';
        $lastVer = GameVersion::orderBy('id', 'desc')->where('game_id', $game->id)->first();
        if($lastVer){
            $version = 'v'.$lastVer->id+1;
        }
        $zip = new ZipArchive();
        $storagePath = 'games/'.$game->id.'/'.$version;
        $absolutePath = Storage::disk('local')->path($storagePath);

        if(!$zip->open($request->file('zipfile')->getRealPath())){
            return response('ZIP file cannot be opened', 400);
        };

        $zip->extractTo($absolutePath);
        $zip->close();

        $gameVersion = new GameVersion();
        $gameVersion->game_id = $game->id;
        $gameVersion->version = $version;
        $gameVersion->storage_path = $storagePath.'/';
        $gameVersion->save();

        
        return response('Upload success', 201);
    }



    public function getGamePath(Request $request, Game $game, String $path){

        $lastVer = GameVersion::orderBy('id', 'desc')->where('game_id', $game->id)->first();
        
        return Storage::disk('local')->response('games/'.$game->id.'/'.'v'.$lastVer->id.'/'.$path);
        // return collect($game)->except(['created_at', 'game_author']);
    }



    public function getTopScores(Request $request, Game $game){
        $allUsers = User::all();
        $content = [];
        foreach($allUsers as $user){
            $lastVersion = GameVersion::orderBy('id', 'desc')->where('game_id', $game->id)->first();
            $scoresOfUser = [];
            if($lastVersion){
                $scoresOfUser = Score::where('user_id', $user->id)->where('game_version_id', $lastVersion->id)->get();
            }
            $allScores = 0;

            foreach($scoresOfUser as $scoreOfUser){
                $allScores = $allScores + $scoreOfUser->score;
            }

            $content[] = [
                'username' => $user->username,
                'score' => $allScores,
                'timestamp' => $user->created_at
            ];
        }

        return response()->json([
            'scores' => $content
        ], 200);
    }



    public function setScore(Request $request, Game $game){
        $request->validate([
            'score' => 'required'
        ]);
        $lastVersion = GameVersion::orderBy('id', 'desc')->where('game_id', $game->id)->first();

        if(!$lastVersion){
            return response()->json([
                'status' => 'invalid',
                'message' => 'Game have not a version!'
            ], 400);
        }
        
        $new_score = new Score();
        $new_score->user_id = $request->user()->id;
        $new_score->game_version_id = $lastVersion->id;
        $new_score->score = $request->get('score');
        $new_score->save();
        return response()->json([
            'status' => 'success'
        ], 201);
    }
}
