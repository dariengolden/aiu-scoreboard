<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['sport_id', 'name', 'slug'];

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function semiFinal1(): ?Game
    {
        return $this->games()->where('round', 'semi_final_1')->first();
    }

    public function semiFinal2(): ?Game
    {
        return $this->games()->where('round', 'semi_final_2')->first();
    }

    public function final(): ?Game
    {
        return $this->games()->where('round', 'final')->first();
    }
}
