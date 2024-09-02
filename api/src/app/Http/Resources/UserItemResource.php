<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this->pivot->item_id,
            'effect' => $this->effect,
            'amount' => $this->pivot->amount
        ];
    }
}
