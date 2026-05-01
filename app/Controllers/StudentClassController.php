<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use App\Models\ClassroomMember;
use App\Models\Student;

class StudentClassController extends Controller
{
    /**
     * Get students by class ID
     * GET /student-class/class/{classId}
     */
    public function getByClass()
    {
        $classId = $this->request->param('classId');

        if (!$classId || $classId === 'undefined') {
            return $this->response->json([
                'success' => true,
                'item' => [],
                'message' => 'Invalid class ID provided'
            ]);
        }

        // Return ClassroomMember with student relation
        // We use ActiveRecord's with() and where() for eager loading
        $model = new ClassroomMember();
        $members = $model->with(['student'])
            ->where(['class_id' => $classId]);

        return $this->response->json([
            'success' => true,
            'item' => $members
        ]);
    }
}
