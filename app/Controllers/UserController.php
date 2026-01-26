<?php

namespace App\Controllers;

use App\Controllers\Base\UserController as BaseController;

class UserController extends BaseController
{
    /**
     * Override methods here to add custom logic.
     * Examples of flexible response formats:
     */

    // Example 1: Direct return array
    public function indexSimple()
    {
        return $this->model->all();
    }

    // Example 2: Return with custom status
    public function createQuick()
    {
        $data = $this->request->all();
        $id = $this->model->create($data);

        // Auto status 201 for created
        return $this->created($this->model->find($id));
    }

    // Example 3: Simple format response
    public function viewSimple()
    {
        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            return $this->simple(null, 'error', 'USER_NOT_FOUND', 404);
        }

        return $this->simple($user, 'success', 'USER_FOUND');
    }

    // Example 4: Raw data return
    public function rawData()
    {
        $users = $this->model->all();
        return $this->raw($users);
    }

    // Example 5: Custom response structure
    public function customFormat()
    {
        $users = $this->model->all();
        return [
            'status' => 'success',
            'code' => 'SUCCESS',
            'data' => $users,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
