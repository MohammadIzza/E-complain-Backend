<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class ComplainResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * $this di sini mewakili data complain yang dikirim ke resource
     * Contoh: jika kita kirim $complain = Complain::find(1)
     * Maka $this->id adalah $complain->id
     * $this->title adalah $complain->title
     * dan seterusnya
     */
    public function toArray(Request $request): array
    {
        // Misalkan kita punya data complain:
        // $complain = [
        //     'id' => 1,
        //     'title' => 'Laptop Rusak',
        //     'description' => 'Laptop tidak bisa nyala',
        //     'user_id' => 1
        // ]

        return [
            "id" => $this->id,
            "user" => new UserResource($this->whenLoaded('user')), 
            "code" => $this->code,
            "title" => $this->title,
            "description"=> $this->description,
            "status"=>$this->status,
            "priority"=>$this->priority,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at,
            "completed_at"=>$this->completed_at            
        ];
    }
}
