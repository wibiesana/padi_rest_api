<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use App\Models\ExamReport;
use Wibiesana\Padi\Core\Request;
use Wibiesana\Padi\Core\Response;

class ExamReportController extends Controller
{
    protected $model;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamReport();
    }

    /**
     * List all reports (for Admin)
     */
    public function index()
    {
        $keyword = $this->request->query('keyword', '');
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)$this->request->query('per-page', 10);
        $eventId = $this->request->query('exam_event_id');

        return $this->model->searchPaginate($keyword, $page, $perPage, null, $eventId);
    }

    /**
     * Submit/Create a report
     */
    public function store()
    {
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'classroom_id' => 'required|integer',
            'supervisor_id' => 'required|integer',
            'student_count' => 'integer',
            'present_count' => 'integer',
            'absent_count' => 'integer',
            'incident_report' => 'string'
        ]);

        $validated['report_date'] = date('Y-m-d H:i:s');

        $id = $this->model->create($validated);

        return $this->created([
            'success' => true,
            'message' => 'Berita Acara berhasil disimpan',
            'item' => $this->model->find($id)
        ]);
    }

    /**
     * Get report by Exam and Classroom
     */
    public function getByExamClass()
    {
        $examId = $this->request->query('exam_id');
        $classroomId = $this->request->query('classroom_id');

        $report = $this->model::findQuery()
            ->where(['exam_id' => $examId, 'classroom_id' => $classroomId])
            ->one();

        return [
            'success' => true,
            'item' => $report
        ];
    }
}
