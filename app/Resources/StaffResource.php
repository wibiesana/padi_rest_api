<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StaffResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nuptk' => $this->nuptk,
            'nip' => $this->nip,
            'nik' => $this->nik,
            'gender' => $this->gender,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth,
            'job_status' => $this->job_status,
            'religion' => $this->religion,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
            'status' => $this->status,

            // Relations
            'id' => $this->whenLoaded('id'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'id_name' => $this->id['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
