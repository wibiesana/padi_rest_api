<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class BillingResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'billing_type' => $this->billing_type,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'description' => $this->description,
            'school_year_id' => $this->school_year_id,
            'semester_id' => $this->semester_id,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'schoolYear' => $this->whenLoaded('schoolYear'),
            'semester' => $this->whenLoaded('semester'),
            'student' => $this->whenLoaded('student'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'schoolYear_name' => $this->schoolYear['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,
            'student_name' => $this->student['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
