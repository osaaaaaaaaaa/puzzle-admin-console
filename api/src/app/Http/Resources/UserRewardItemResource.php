<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRewardItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id' => $this['id'],
            'name' => $this['name'],
            'type' => $this['type'],
            'effect' => $this['effect'],
            'description' => $this['description'],
            'amount' => $this['amount']
        ];
    }
}
