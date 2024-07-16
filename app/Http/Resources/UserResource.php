<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'identify' => $this->id,
            'name' => strtoupper($this->name),
            'email' => $this->email,
            'payment' => $this->payment_id,
            'role_id' => $this->role_id,
            'plan_id' => $this->plan_id,
            'created' =>Carbon::make($this->created_at),
        ];
    }
}
