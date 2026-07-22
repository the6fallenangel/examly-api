<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'taker_name' => $this->taker_name,
            'taker_email' => $this->taker_email,
            'verified_at' => $this->verified_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'answers_count' => $this->whenCounted('answers'),
            'answers' => AttemptAnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
