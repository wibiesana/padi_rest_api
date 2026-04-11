<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StudentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nis' => $this->nis,
            'nisn' => $this->nisn,
            'jenis_kelamin' => $this->jenis_kelamin,
            'gender' => $this->jenis_kelamin,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'agama' => $this->agama,
            'status' => $this->status,
            'anak_ke' => $this->anak_ke,
            'alamat' => $this->alamat,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'desa_kelurahan' => $this->desa_kelurahan,
            'kecamatan' => $this->kecamatan,
            'kode_pos' => $this->kode_pos,
            'no_telp' => $this->no_telp,
            'email' => $this->email,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'father_occupation' => $this->father_occupation,
            'mother_occupation' => $this->mother_occupation,
            'guardian_name' => $this->guardian_name,
            'guardian_address' => $this->guardian_address,
            'guardian_phone' => $this->guardian_phone,
            'guardian_occupation' => $this->guardian_occupation,
            'photo' => $this->photo,
            'photo_url' => $this->photo_url,


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
