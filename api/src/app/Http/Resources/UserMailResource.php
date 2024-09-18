<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserMailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mail_id' => $this->mail_id,
            'title' => $this->title,
            'text' => $this->text,
            'is_received' => $this->is_received,
            'created_at' => $this->created_at->format('Y-m-d'),
            'elapsed_days' => $this->elapsed_days
        ];
    }
}
