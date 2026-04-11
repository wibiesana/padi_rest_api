<?php

namespace App\Controllers;

use App\Controllers\Base\TeacherUploadResultController as BaseController;

use App\Resources\TeacherUploadResultResource;

class TeacherUploadResultController extends BaseController
{
    /**
     * Get all teacheruploadresults with pagination and optional filter
     * GET /teacheruploadresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name', 'teacherUpload:id,name']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search', '');

        $filters = [];
        if ($this->request->query('teacher_upload_id')) {
            $filters['teacher_upload_id'] = (int)$this->request->query('teacher_upload_id');
        }

        $result = $this->model->searchPaginate($search, $page, $perPage, null, $filters);

        return TeacherUploadResultResource::collection($result);
    }

    /**
     * Download uploaded file
     * GET /teacher-upload-result/{id}/download
     */
    public function download()
    {
        $id = $this->request->param('id');
        $result = $this->model->find($id);

        if (!$result || empty($result['upload_file'])) {
            throw new \Exception('File not found', 404);
        }

        // Assuming files are stored in a specific directory, e.g., storage/uploads
        $filePath = dirname(__DIR__, 2) . '/storage/uploads/' . ($result['upload_file'] ?? '');

        if (!file_exists($filePath)) {
            throw new \Exception('File physical not found on server', 404);
        }

        return $this->response->download($filePath, $result['upload_file'] ?? '');
    }
}
