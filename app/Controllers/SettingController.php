<?php

namespace App\Controllers;

use App\Controllers\Base\SettingController as BaseController;

use App\Resources\SettingResource;

class SettingController extends BaseController
{
    /**
     * Override methods here to add custom logic.
     */

    /**
     * Get active settings with pagination
     * GET /settings
     */
    public function index()
    {
        // Fetch only the first active setting using the query builder ->one()
        $setting = $this->model->findBuilder()->where(['status' => 1])->one();

        if (!$setting) {
            // Fallback: if no active setting, send empty or handle accordingly
            return $this->json([
                'success' => true,
                'message' => 'No active setting found',
                'item' => null
            ]);
        }

        return SettingResource::make($setting);
    }

    public function store()
    {
        $this->requireRole('superadmin');

        $validated = $this->validate([
            'setting' => 'string'
        ]);

        // Force status to 1 for new settings so they are visible
        $validated['status'] = 1;

        try {
            $id = $this->model->create($validated);
            $setting = $this->model->find($id);
            return $this->created(SettingResource::make($setting));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create setting', $e);
        }
    }

    public function update()
    {
        $this->requireRole('superadmin');

        $id = $this->request->param('id');
        $setting = $this->model->find($id);

        if (!$setting) {
            throw new \Exception('Setting not found', 404);
        }

        $validated = $this->validate([
            'setting' => 'string'
        ]);

        try {
            $this->model->update($id, $validated);
            return SettingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update setting', $e);
        }
    }

    public function destroy()
    {
        $this->requireRole('superadmin');
        return parent::destroy();
    }
}
