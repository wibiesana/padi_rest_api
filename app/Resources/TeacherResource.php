<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class TeacherResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'data_sekolah_id' => $this->data_sekolah_id,
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
            'no_hp' => $this->no_hp,
            'email' => $this->email,
            'photo' => $this->photo,
            'status' => $this->status,
            'asc_id' => $this->asc_id,
            'asc_partner_id' => $this->asc_partner_id,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'id' => $this->whenLoaded('id'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,
            'id_name' => $this->id['name'] ?? null,

        ];
    }
}
