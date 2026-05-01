<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamReportResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'classroom_id' => $this->classroom_id,
            'supervisor_id' => $this->supervisor_id,
            'report_date' => $this->report_date,
            'student_count' => $this->student_count,
            'present_count' => $this->present_count,
            'absent_count' => $this->absent_count,
            'incident_report' => $this->incident_report,

            // Relations

            // Flattened Fields

        ];
    }
}
