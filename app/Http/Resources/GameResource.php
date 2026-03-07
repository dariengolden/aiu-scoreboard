<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matchNumber' => $this->match_number,
            'status' => $this->status,
            'scheduledAt' => $this->scheduled_at,
            'location' => $this->location,
            'scoreHome' => $this->score_home,
            'scoreAway' => $this->score_away,
            'currentPeriod' => $this->current_period,
            'category' => [
                'name' => $this->category->name,
                'sport' => [
                    'name' => $this->category->sport->name,
                    'icon' => $this->category->sport->icon,
                ],
            ],
            'teamHome' => [
                'name' => $this->teamHome->name,
                'colorHex' => $this->teamHome->color_hex,
            ],
            'teamAway' => [
                'name' => $this->teamAway->name,
                'colorHex' => $this->teamAway->color_hex,
            ],
        ];
    }
}
