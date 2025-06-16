<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->formatcreateatjam(),
            'update_at' => $this->formatupdateatjam(),
        ];
    }

    public function formatcreateatjam()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d H:i:s');
    }
    public function formatupdateatjam()
    {
        return Carbon::parse($this->update_at)->format('Y-m-d H:i:s');
    }
}
