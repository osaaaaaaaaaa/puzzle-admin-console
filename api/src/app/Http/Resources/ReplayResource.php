<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'd_signal_id' => $this->distress_signal_id,
            'replay_data' => json_decode($this->replay_data, true),
        ];
    }
}
