<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistressSignalUserProfileResource extends JsonResource
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
            'is_agreement' => $this['is_agreement']
        ];
    }
}
