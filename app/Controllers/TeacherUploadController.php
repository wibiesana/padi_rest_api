<?php

namespace App\Controllers;

use App\Controllers\Base\TeacherUploadController as BaseController;

use App\Resources\TeacherUploadResource;

class TeacherUploadController extends BaseController
{
    /**
     * Get validation rules
     */
    protected function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'target' => 'integer',
            'description' => 'string|max:255',
            'status' => 'integer',
            'status' => 'integer',
            'assign_to' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'semester_id' => 'integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ];
    }

    /**
     * Create new teacherupload
     * POST /teacheruploads
     */
    public function store()
    {
        $validated = $this->validate($this->getValidationRules());

        // Ensure datetime format
        $validated['start_date'] = date('Y-m-d H:i:s', strtotime($validated['start_date']));
        $validated['end_date'] = date('Y-m-d H:i:s', strtotime($validated['end_date']));

        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            $this->model->with(['createdBy:id,name', 'updatedBy:id,name', 'semester:id,name']);

            $teacherupload = $this->model->find($id);
            return $this->created(TeacherUploadResource::make($teacherupload));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create teacherupload', $e);
        }
    }

    /**
     * Update teacherupload
     * PUT /teacheruploads/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $teacherupload = $this->model->find($id);

        if (!$teacherupload) {
            throw new \Exception('TeacherUpload not found', 404);
        }

        $validated = $this->validate($this->getValidationRules());

        // Ensure datetime format
        $validated['start_date'] = date('Y-m-d H:i:s', strtotime($validated['start_date']));
        $validated['end_date'] = date('Y-m-d H:i:s', strtotime($validated['end_date']));

        try {
            $this->model->update($id, $validated);
            // Auto-generated eager loading
            $this->model->with(['createdBy:id,name', 'updatedBy:id,name', 'semester:id,name']);

            return TeacherUploadResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update teacherupload', $e);
        }
    }
}
