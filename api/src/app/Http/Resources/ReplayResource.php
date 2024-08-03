<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'replay_data' => $this->replay_data,
            'guest_data' => $this->guest_data
        ];
    }
}
