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

    public function getGlowColorClass(): string
    {
        return match (strtolower($this->name)) {
            'red' => 'shadow-red-500/40',
            'blue' => 'shadow-blue-500/40',
            'purple' => 'shadow-purple-500/40',
            'pink' => 'shadow-pink-500/40',
            default => 'shadow-gray-500/40',
        };
    }

    public function getBarGradientStyle(): string
    {
        return match (strtolower($this->name)) {
            'red' => 'background: linear-gradient(90deg, #ef4444, #f97316)',
            'blue' => 'background: linear-gradient(90deg, #3b82f6, #06b6d4)',
            'purple' => 'background: linear-gradient(90deg, #a855f7, #8b5cf6)',
            'pink' => 'background: linear-gradient(90deg, #ec4899, #f43f5e)',
            default => 'background: linear-gradient(90deg, #6b7280, #9ca3af)',
        };
    }
}
