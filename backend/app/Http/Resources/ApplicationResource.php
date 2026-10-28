<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'company' => $this->company, 'role' => $this->role,
            'location' => $this->location ?? '', 'url' => $this->url,
            'salary_min' => $this->salary_min, 'status' => $this->status,
            'notes' => $this->notes, 'applied_on' => $this->applied_on->format('Y-m-d'),
            'remote' => $this->remote,
        ];
    }
}
