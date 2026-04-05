<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'question' => $this->question,
            'answer'   => $this->answer,

            $this->mergeWhen($request->user()?->is_admin, [
                'is_published' => (bool)$this->is_published,
                'sort_order'   => $this->sort_order,
                'created_at'   => $this->created_at?->toDateTimeString(),
                'updated_at'   => $this->updated_at?->toDateTimeString(),
            ]),
        ];
    }
}
