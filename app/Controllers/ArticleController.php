<?php

namespace App\Controllers;

use App\Controllers\Base\ArticleController as BaseController;
use Wibiesana\Padi\Core\Auth;

class ArticleController extends BaseController
{
    /**
     * Override index to filter by created_by for teachers
     */
    public function index()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $this->model->where(['created_by' => $userId]);
        }
        return parent::index();
    }

    /**
     * Override all to filter by created_by for teachers
     */
    public function all()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $this->model->where(['created_by' => $userId]);
        }
        return parent::all();
    }

    /**
     * Create new article with class associations
     */
    public function store()
    {
        $response = parent::store();
        $payload = $this->request->all();

        if (isset($payload['for_class']) && $response instanceof \App\Resources\ArticleResource) {
            $articleId = $response->id;
            $this->syncClasses($articleId, $payload['for_class']);

            // Reload to include relations
            $this->model->with(['createdBy:id,name', 'subject:id,name', 'updatedBy:id,name', 'classes']);
            return \App\Resources\ArticleResource::make($this->model->find($articleId));
        }

        return $response;
    }

    /**
     * Update article with class associations
     */
    public function update()
    {
        $response = parent::update();
        $payload = $this->request->all();
        $id = $this->request->param('id');

        if (isset($payload['for_class']) && $response instanceof \App\Resources\ArticleResource) {
            $this->syncClasses($id, $payload['for_class']);

            // Reload to include relations
            $this->model->with(['createdBy:id,name', 'subject:id,name', 'updatedBy:id,name', 'classes']);
            return \App\Resources\ArticleResource::make($this->model->find($id));
        }

        return $response;
    }

    protected function syncClasses($articleId, array $classIds)
    {
        // Delete existing associations
        \App\Models\ArticleClass::findQuery()
            ->where(['article_id' => $articleId])
            ->delete();

        // Insert new associations
        if (!empty($classIds)) {
            $rows = [];
            foreach ($classIds as $classId) {
                if (!empty($classId)) {
                    $rows[] = [
                        'article_id' => $articleId,
                        'class_id' => $classId
                    ];
                }
            }
            if (!empty($rows)) {
                $itemClass = new \App\Models\ArticleClass();
                $itemClass->batchInsert($rows);
            }
        }
    }
}
