<?php

namespace App\Controllers;

use App\Controllers\Base\CalendarAcademicController as BaseController;

class CalendarAcademicController extends BaseController
{
    /**
     * Create new calendaracademic
     */
    public function store()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'string',
            'start_date' => 'required',
            'end_date' => 'string',
            'color' => 'string|max:10',
            'is_holiday' => 'integer',
            'status' => 'integer'
        ]);

        try {
            $id = $this->model->create($validated);
            $calendaracademic = $this->model->find($id);
            return $this->created(\App\Resources\CalendarAcademicResource::make($calendaracademic));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create calendaracademic', $e);
        }
    }

    /**
     * Update calendaracademic
     */
    public function update()
    {
        $id = $this->request->param('id');
        $calendaracademic = $this->model->find($id);

        if (!$calendaracademic) {
            throw new \Exception('CalendarAcademic not found', 404);
        }

        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'string',
            'start_date' => 'required',
            'end_date' => 'string',
            'color' => 'string|max:10',
            'is_holiday' => 'integer',
            'status' => 'integer'
        ]);

        try {
            $this->model->update($id, $validated);
            return \App\Resources\CalendarAcademicResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update calendaracademic', $e);
        }
    }
}
