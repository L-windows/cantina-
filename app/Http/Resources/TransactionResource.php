<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'operator' => $this->operator ? $this->operator->name : null,
            'created_at' => $this->created_at,
        ];
    }
}
