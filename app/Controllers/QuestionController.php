<?php

namespace App\Controllers;

use App\Controllers\Base\QuestionController as BaseController;

class QuestionController extends BaseController
{
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

        $page = max(1, (int)$this->request->query('page', 1)); // Min page 1
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10))); // Max 100 per page
        $search = $this->request->query('search');
        $qbId = $this->request->query('question_bank_id');

        $conditions = [];
        if ($qbId) {
            $conditions['question_bank_id'] = $qbId;
        }

        if ($search) {
            // Limit search query length to prevent abuse
            $search = substr($search, 0, 255);
            if ($qbId) {
                $result = $this->model->searchByBankPaginate($search, (int)$qbId, $page, $perPage);
            } else {
                $result = $this->model->searchPaginate($search, $page, $perPage);
            }
        } else {
            $result = $this->model->paginate($page, $perPage, $conditions);
        }

        return \App\Resources\QuestionResource::collection($result);
    }

    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

        $search = $this->request->query('search');
        $qbId = $this->request->query('question_bank_id');

        if ($search) {
            if ($qbId) {
                return \App\Resources\QuestionResource::collection($this->model->searchByBank($search, (int)$qbId));
            }
            return \App\Resources\QuestionResource::collection($this->model->search($search));
        }

        if ($qbId) {
            return \App\Resources\QuestionResource::collection($this->model->where(['question_bank_id' => $qbId]));
        }

        return \App\Resources\QuestionResource::collection($this->model->all());
    }

    public function batchStore()
    {
        $data = $this->request->all();
        $records = $data['records'] ?? [];
        $qbId = $data['question_bank_id'] ?? null;

        if (empty($records)) {
            throw new \Exception('No data to store', 400);
        }

        if (!$qbId) {
            throw new \Exception('Question bank ID is required', 400);
        }

        $successCount = 0;
        $errors = [];

        foreach ($records as $index => $record) {
            try {
                $this->model->create([
                    'type' => $record['type'] ?? 1,
                    'question' => $record['question'] ?? '',
                    'answer' => $record['answer'] ?? '',
                    'answer_discussion' => $record['answer_discussion'] ?? '',
                    'options_json' => isset($record['options_json']) ? $record['options_json'] : (isset($record['options']) ? json_encode($record['options']) : null),
                    'number_of_choice' => $record['number_of_choice'] ?? 5,
                    'answer_score' => $record['answer_score'] ?? 1,
                    'question_bank_id' => $qbId,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Record $index: " . $e->getMessage();
            }
        }

        return [
            'success_count' => $successCount,
            'errors' => $errors
        ];
    }
}
