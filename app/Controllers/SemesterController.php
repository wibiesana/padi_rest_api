<?php

namespace App\Controllers;

use App\Controllers\Base\SemesterController as BaseController;
use Wibiesana\Padi\Core\Database;

class SemesterController extends BaseController
{
    public function setActive()
    {
        $id = $this->request->param('id');
        $item = $this->model->find($id);

        if (!$item) {
            throw new \Exception('Semester not found', 404);
        }

        try {
            return Database::transaction(function () use ($id) {
                // Deactivate all semesters
                $this->model->updateAll(['status' => 0]);

                // Activate the selected semester
                $this->model->update($id, ['status' => 1]);

                return [
                    'success' => true,
                    'message' => 'Semester activated successfully',
                    'item' => $this->model->find($id)
                ];
            });
        } catch (\PDOException $e) {
            $this->databaseError('Failed to activate semester', $e);
        }
    }
}
