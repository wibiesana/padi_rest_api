<?php

namespace App\Controllers;

use App\Controllers\Base\DepartmentController as BaseController;

class DepartmentController extends BaseController
{
    /**
     * Get all departments with pagination
     * GET /departments
     */
    public function index()
    {
        $semesterId = $this->request->query('semester_id');

        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'teacher:id,name', 'semester:id,name', 'updatedBy:id,username']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search');

        if ($search) {
            $search = substr($search, 0, 255);
            $result = $this->model->searchWithSemester($search, $page, $perPage, $semesterId ? (int)$semesterId : null);
        } else {
            $result = $this->model->paginateWithSemester($page, $perPage, $semesterId ? (int)$semesterId : null);
        }

        return \App\Resources\DepartmentResource::collection($result);
    }

    /**
     * Get all departments without pagination
     * GET /departments/all
     */
    public function all()
    {
        $semesterId = $this->request->query('semester_id');

        // Auto-generated eager loading
        $this->model->with(['createdBy:id,name', 'teacher:id,name', 'semester:id,name', 'updatedBy:id,name']);

        $search = $this->request->query('search');
        if ($search) {
            return \App\Resources\DepartmentResource::collection($this->model->search($search));
        }

        if ($semesterId) {
            return \App\Resources\DepartmentResource::collection($this->model->where(['semester_id' => $semesterId]));
        }

        return \App\Resources\DepartmentResource::collection($this->model->all());
    }
}
