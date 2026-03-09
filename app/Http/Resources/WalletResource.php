<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'balance' => $this->balance,
            'transactions_count' => $this->transactions()->count(),
            'updated_at' => $this->updated_at,
        ];
    }
}
