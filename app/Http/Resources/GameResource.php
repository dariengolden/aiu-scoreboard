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
            // Keep raw datetime for potential future use, but also provide a
            // preformatted time string so the client can display times without
            // introducing timezone shifts.
            'scheduledAt' => $this->scheduled_at,
            'scheduledEndAt' => $this->scheduled_end_at,
            'scheduledTime' => $this->scheduled_at?->format('g:ia'),
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
            'eventType' => $this->event_type,
            'eventTitle' => $this->event_title,
        ];
    }
}
