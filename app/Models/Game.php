<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\GameVersion;
use App\Models\Score;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['author', 'scoreCount', 'gamePath'];

    public function getAuthorAttribute()
    {
        return $this->gameAuthor->username;
    }
    public function gameAuthor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getScoreCountAttribute()
    {
        $game_versions = GameVersion::all()->where('game_id', $this->id);
        $count = 0;
        foreach($game_versions as $ver){
            $count = $count + Score::all()->where('game_version_id', $ver->id)->count();
        }
        return $count;
    }


    public function getGamePathAttribute()
    {
        return "/games/".$this->slug."/1/";
    }
    protected $hidden = [
        'deleted_at'
    ];
}
