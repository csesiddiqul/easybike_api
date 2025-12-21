<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'expiration_reminder' => $this->expiration_reminder,
            'access_action_minutes' => $this->access_action_minutes,
            'logo' => $this->logo,
            'title' => $this->title,
            'email' => $this->email,
            'youtube' => $this->youtube,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
        ];
    }
}
