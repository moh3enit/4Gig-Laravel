<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nationality' => $this->nationality,
            'birth_date' => $this->birth_date,
            'gender' => $this->gender_name,
            'availability_on_demand' => $this->availability_on_demand,
            'per_hour' => $this->per_hour,
            'avatar' => $this->getFirstMediaUrl(Profile::COLLECTION_NAME),
            'user' => $this->user,
            'skills' => $this->skills,
            'spoken_languages' => $this->spokenLanguages,
        ];
    }
}