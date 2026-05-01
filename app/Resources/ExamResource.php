<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'question_bank_id' => $this->question_bank_id,
            'subject_id' => $this->subject_id,
            'semester_id' => $this->semester_id,
            'status' => $this->status,
            'token' => $this->token,
            'has_token' => !empty($this->token),
            'test_duration' => $this->test_duration,
            'use_dynamic_token' => $this->use_dynamic_token,
            'show_pg' => $this->show_pg,
            'show_essay' => $this->show_essay,
            'show_result' => $this->show_result,
            'percentage_mc_value' => $this->percentage_mc_value,
            'percentage_essay_value' => $this->percentage_essay_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_random' => $this->is_random,
            'randomize_questions' => $this->randomize_questions,
            'randomize_options' => $this->randomize_options,
            'lock_on_switch' => $this->lock_on_switch,
            'require_supervisor' => $this->require_supervisor,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'questionBank' => $this->whenLoaded('questionBank'),
            'subject' => $this->whenLoaded('subject'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'classes' => $this->whenLoaded('classes'),
            'supervisors' => $this->whenLoaded('supervisors'),
            'examiners' => $this->whenLoaded('examiners'),
            'supervisorAssignments' => $this->whenLoaded('supervisorAssignments'),
            'participants' => $this->getParticipants(),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'questionBank_name' => $this->questionBank['name'] ?? null,
            'subject_name' => $this->subject['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,
            'working_student_count' => (int)($this->working_student_count ?? $this->getWorkingCount()),
            'exam_results' => $this->whenLoaded('examresults'),
        ];
    }

    private function getWorkingCount(): int
    {
        // Try direct attribute access if set in controller via setAttribute or array access
        $count = $this->resource->working_student_count ?? $this->resource['working_student_count'] ?? null;
        if (!is_null($count)) return (int)$count;

        $results = $this->whenLoaded('examresults');
        if (is_null($results) || !is_iterable($results)) return 0;

        $count = 0;
        foreach ($results as $res) {
            $status = $res->status ?? $res['status'] ?? 1;
            if ((int)$status >= 2) $count++;
        }
        return $count;
    }

    private function getParticipants()
    {
        $results = $this->whenLoaded('examresults');
        if (is_null($results) || !is_iterable($results)) return [];

        $participants = [];
        foreach ($results as $res) {
            $student = $res->student ?? $res['student'] ?? null;
            if ($student) {
                $participants[] = [
                    'id' => $student->id ?? $student['id'] ?? null,
                    'name' => $student->name ?? $student['name'] ?? null,
                    'nisn' => $student->nisn ?? $student['nisn'] ?? null,
                ];
            }
        }
        return $participants;
    }
}
