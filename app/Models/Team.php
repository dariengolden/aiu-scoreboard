<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = ['name', 'color_hex'];

    public function homeGames(): HasMany
    {
        return $this->hasMany(Game::class, 'team_home_id');
    }

    public function awayGames(): HasMany
    {
        return $this->hasMany(Game::class, 'team_away_id');
    }

    public function wonGames(): HasMany
    {
        return $this->hasMany(Game::class, 'winner_id');
    }

    public function getTextColorClass(): string
    {
        return match (strtolower($this->name)) {
            'red' => 'text-red-500',
            'blue' => 'text-blue-500',
            'purple' => 'text-purple-500',
            'pink' => 'text-pink-500',
            default => 'text-gray-500',
        };
    }

    public function getBgColorClass(): string
    {
        return match (strtolower($this->name)) {
            'red' => 'bg-red-500',
            'blue' => 'bg-blue-500',
            'purple' => 'bg-purple-500',
            'pink' => 'bg-pink-500',
            default => 'bg-gray-500',
        };
    }
}
