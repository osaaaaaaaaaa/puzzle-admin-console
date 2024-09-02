<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRecommendedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this['id'],
            'name' => $this['name'],
            'title' => $this['title'],
            'stage_id' => $this['stage_id'],
            'icon_id' => $this['icon_id'],
            'score' => $this['score'],
            'is_follower' => $this['is_follower']
        ];
    }
}
