<?php

namespace App\Controllers;

use App\Controllers\Base\TeacherController as BaseController;
use Wibiesana\Padi\Core\Database;
use Wibiesana\Padi\Core\Auth;

class TeacherController extends BaseController
{
    /**
     * Store a new teacher (User + Teacher)
     */
    public function store()
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|string|max:100|email|unique:users,email',
            'nip' => 'string|max:50|unique:teacher,nip',
            'nik' => 'string|max:50|unique:teacher,nik',
            'nuptk' => 'string|max:50',
            'gender' => 'string|max:20',
            'place_of_birth' => 'string|max:100',
            'date_of_birth' => 'string',
            'religion' => 'string|max:50',
            'address' => 'string|max:255',
            'phone' => 'string|max:20',
            'photo' => 'string|max:255',
            'job_status' => 'string|max:20',
            'status' => 'integer'
        ];

        $data = $this->validate($rules);

        // Handle default password
        $rawPassword = $this->request->input('password');
        $data['password'] = !empty($rawPassword) ? $rawPassword : '123456';

        $userModel = new \App\Models\User();

        try {
            return Database::transaction(function () use ($data, $userModel) {
                // 1. Create User
                // $userModel = new \App\Models\User(); // Instantiated outside now

                // Determine username: NIP > NIK > Email prefix
                $username = !empty($data['nip']) ? $data['nip'] : (!empty($data['nik']) ? $data['nik'] : explode('@', $data['email'])[0]);

                $userData = [
                    'email' => $data['email'],
                    'username' => $username,
                    'password' => $data['password'] ?? '123456',
                    'role' => 'teacher',
                    'status' => 1
                ];

                $userId = $userModel->create($userData);
                if (!$userId) {
                    throw new \Exception("Failed to create user account");
                }

                // 2. Create Teacher linked to User ID
                $data['id'] = $userId;

                $this->model->create($data);


                return [
                    'success' => true,
                    'item' => $this->model->find($userId),
                    'message' => 'Teacher created successfully'
                ];
            });
        } catch (\Exception $e) {
            return $this->response->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Batch create teachers from import
     */
    public function batchCreate()
    {
        $items = $this->request->input('items');
        if (!is_array($items)) {
            $this->setStatusCode(400);
            return [
                'success' => false,
                'message' => 'Invalid data format. Expected array of items.'
            ];
        }

        try {
            return Database::transaction(function () use ($items) {
                $userModel = new \App\Models\User();
                $teacherModel = new \App\Models\Teacher();
                $userRows = [];
                $usernames = [];

                // 1. Prepare User Data & Pre-Map Teacher Data
                $mappedItems = [];

                $currUserId = Auth::userId();
                $nowUnix = time();
                $nowDateTime = date('Y-m-d H:i:s');

                foreach ($items as $item) {
                    // Basic validation
                    if (empty($item['name']) || empty($item['email'])) {
                        continue;
                    }

                    $username = explode('@', $item['email'])[0];
                    if (!empty($item['nip'])) $username = $item['nip'];
                    elseif (!empty($item['nik'])) $username = $item['nik'];

                    // Avoid duplicate usernames in the batch itself
                    if (in_array($username, $usernames)) continue;

                    $usernames[] = $username;

                    $userRows[] = [
                        'email' => $item['email'],
                        'username' => $username,
                        'password' => password_hash($item['password'] ?? '123456', PASSWORD_BCRYPT),
                        'role' => 'teacher',
                        'status' => 'active',
                        'created_at' => $nowUnix,
                        'updated_at' => $nowUnix,
                    ];

                    $mappedItems[$username] = [
                        'name' => $item['name'],
                        // 'email' => $item['email'], // Teacher table has email column? Yes.
                        'email' => $item['email'],
                        'nip' => $item['nip'] ?? null,
                        'nik' => $item['nik'] ?? null,
                        'nuptk' => $item['nuptk'] ?? null,
                        'gender' => $item['gender'] ?? null,
                        'place_of_birth' => $item['place_of_birth'] ?? null,
                        'date_of_birth' => $item['date_of_birth'] ?? null,
                        'religion' => $item['religion'] ?? null,
                        'no_hp' => $item['phone'] ?? null,
                        'address' => $item['address'] ?? null,
                        'job_status' => $item['status'] ?? 'Honorary',
                        'status' => $item['status'] ?? 1,
                        // Teacher uses Shared PK with User
                        'id' => null,
                        'created_at' => $nowDateTime,
                        'updated_at' => $nowDateTime,
                        'created_by' => $currUserId,
                        'updated_by' => $currUserId,
                    ];
                }

                if (empty($userRows)) {
                    throw new \Exception("No valid data to import");
                }

                // 2. Batch Insert Users
                if (!$userModel->batchInsert($userRows)) {
                    throw new \Exception("Failed to batch insert users");
                }

                // 3. Retrieve User IDs
                $users = $userModel::findQuery()
                    ->select(['id', 'username'])
                    ->where(['username' => $usernames])
                    ->all();

                $userMap = [];
                foreach ($users as $u) {
                    $userMap[$u['username']] = $u['id'];
                }

                // 4. Bind IDs to Teacher Data
                $teacherRows = [];
                foreach ($mappedItems as $username => $sData) {
                    if (isset($userMap[$username])) {
                        $sData['id'] = $userMap[$username];
                        $teacherRows[] = $sData;
                    }
                }

                if (empty($teacherRows)) {
                    throw new \Exception("Failed to map users to teachers");
                }

                // 5. Batch Insert Teachers
                if (!$teacherModel->batchInsert($teacherRows)) {
                    throw new \Exception("Failed to batch insert teachers");
                }

                return [
                    'success' => true,
                    'count' => count($teacherRows),
                    'message' => count($teacherRows) . ' teachers imported successfully.'
                ];
            });

            return $result;
        } catch (\Throwable $e) {
            $this->setStatusCode(400);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check for duplicates
     */
    public function checkUniqueness()
    {
        $emails = $this->request->input('emails', []);
        $niks = $this->request->input('niks', []);
        $nips = $this->request->input('nips', []);

        $result = [
            'existing_emails' => [],
            'existing_niks' => [],
            'existing_nips' => []
        ];

        if (!empty($emails)) {
            $result['existing_emails'] = $this->model::findQuery()
                ->where(['email' => $emails])
                ->select('email')
                ->column();
        }

        if (!empty($niks)) {
            $result['existing_niks'] = $this->model::findQuery()
                ->where(['nik' => $niks])
                ->select('nik')
                ->column();
        }

        if (!empty($nips)) {
            $result['existing_nips'] = $this->model::findQuery()
                ->where(['nip' => $nips])
                ->select('nip')
                ->column();
        }

        return array_merge(['success' => true], $result);
    }

    /**
     * Delete teacher and associated user
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $teacher = $this->model->find($id);

        if (!$teacher) {
            throw new \Exception('Teacher not found', 404);
        }

        try {
            return Database::transaction(function () use ($id) {
                try {
                    // Attempt to delete Teacher record first
                    $this->model->delete($id);

                    // If successful, attempt to delete User record
                    $userModel = new \App\Models\User();
                    $userModel->delete($id);

                    return $this->noContent();
                } catch (\PDOException $e) {
                    // If deletion fails (likely due to foreign key constraint)
                    // Set status to 0 for Teacher and User
                    $this->model->update($id, ['status' => 0]);

                    $userModel = new \App\Models\User();
                    $userModel->update($id, ['status' => 'inactive']);

                    return [
                        'success' => true,
                        'message' => 'Teacher has relations and was deactivated instead of deleted.',
                        'item' => $this->model->find($id)
                    ];
                }
            });
        } catch (\Exception $e) {
            return $this->response->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all inactive teachers
     * GET /teacher/not-active-teacher
     */
    public function getInactive()
    {
        $results = $this->model::findQuery()
            ->where(['status' => 0])
            ->orderBy('name ASC')
            ->all();

        return [
            'success' => true,
            'item' => $results
        ];
    }
}
