<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StageResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'stage_id' => $this->stage_id,
            'is_medal1' => $this->is_medal1,
            'is_medal2' => $this->is_medal2,
            'score' => $this->score,
        ];
    }
}
