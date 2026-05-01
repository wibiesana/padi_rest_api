<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;
use App\Resources\ExamResource;
use App\Resources\QuestionBankResource;

class ExamEventResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'school_year_id' => $this->school_year_id,
            'semester_id' => $this->semester_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'total_students' => $this->total_students,

            // Relationships
            'schoolYear' => $this->whenLoaded('schoolYear'),
            'semester' => $this->whenLoaded('semester'),
            'exams' => ExamResource::collection($this->whenLoaded('exams')),
            'working_student_count' => $this->getEventWorkingCount(),
            'questionBanks' => QuestionBankResource::collection($this->whenLoaded('questionBanks')),
        ];
    }

    private function getEventWorkingCount(): int
    {
        // Use pre-calculated attribute if provided by controller
        $count = $this->resource->working_student_count ?? $this->resource['working_student_count'] ?? null;
        if (!is_null($count)) return (int)$count;

        $exams = $this->whenLoaded('exams');
        if (is_null($exams) || !is_iterable($exams)) return 0;

        $totalCount = 0;
        foreach ($exams as $exam) {
            // Prefer pre-calculated attribute on individual exam models to avoid N+1 or iteration
            $examCount = $exam->working_student_count ?? $exam['working_student_count'] ?? null;

            if (!is_null($examCount)) {
                $totalCount += (int)$examCount;
            } else {
                // Fallback to manual count if examresults is loaded
                $results = $exam->examresults ?? $exam['examresults'] ?? null;
                if ($results && is_iterable($results)) {
                    foreach ($results as $res) {
                        $status = $res->status ?? $res['status'] ?? 0;
                        if ((int)$status >= 2) $totalCount++;
                    }
                }
            }
        }
        return $totalCount;
    }
}
