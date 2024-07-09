<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFollowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'following_user_name' => $this->name,
            'is_agreement' => $this->is_agreement
        ];
    }
}
