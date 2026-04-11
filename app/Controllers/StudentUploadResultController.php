<?php

namespace App\Controllers;

use App\Controllers\Base\StudentUploadResultController as BaseController;
use App\Resources\StudentUploadResultResource;

class StudentUploadResultController extends BaseController
{
    /**
     * Get all studentuploadresults with pagination and optional filter
     * GET /studentuploadresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['student:id,name', 'studentUpload:id,name']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search', '');

        $filters = [];
        if ($this->request->query('student_upload_id')) {
            $filters['student_upload_id'] = (int)$this->request->query('student_upload_id');
        }
        if ($this->request->query('student_id')) {
            $filters['student_id'] = (int)$this->request->query('student_id');
        }

        $result = $this->model->searchPaginate($search, $page, $perPage, null, $filters);

        return StudentUploadResultResource::collection($result);
    }

    /**
     * Download uploaded file
     * GET /student-upload-result/{id}/download
     */
    public function download()
    {
        $id = $this->request->param('id');
        $result = $this->model->find($id);

        if (!$result || empty($result['upload_file'])) {
            throw new \Exception('File not found', 404);
        }

        $filePath = dirname(__DIR__, 2) . '/storage/uploads/' . ($result['upload_file'] ?? '');

        if (!file_exists($filePath)) {
            throw new \Exception('File physical not found on server', 404);
        }

        return $this->response->download($filePath, $result['upload_file'] ?? '');
    }
}
