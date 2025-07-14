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
            'id' => $this->id_user,
            'name' => $this->user_name,
            'email' => $this->email,
            'username' => $this->username,
            'nik' => $this->nik,
            'jenis_kelamin' => $this->jenis_kelamin,
            'tanggal_lahir' => $this->tanggal_lahir,
            'phone' => $this->phone,
            'address' => $this->address,
            'photo' => $this->photo,
            'fcm_token' => $this->fcm_token,
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
