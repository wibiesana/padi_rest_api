<?php

namespace App\Controllers;

use App\Controllers\Base\StudentController as BaseController;
use Wibiesana\Padi\Core\Database;
use Wibiesana\Padi\Core\Auth;

class StudentController extends BaseController
{
    /**
     * Store a new student (User + Student)
     */
    public function store()
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|string|max:100|email|unique:users,email',
            'nis' => 'string|max:50|unique:student,nis',
            'nisn' => 'string|max:50|unique:student,nisn',
            'gender' => 'string|max:20',
            'place_of_birth' => 'string|max:100',
            'date_of_birth' => 'string',
            'religion' => 'string|max:50',
            'address' => 'string|max:255',
            'phone' => 'string|max:20',
            'photo' => 'string|max:255',
            'status' => 'string|max:20',
            'father_name' => 'string|max:100',
            'mother_name' => 'string|max:100',
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

                // Determine username: NISN > NIS > Email prefix
                $username = !empty($data['nisn']) ? $data['nisn'] : (!empty($data['nis']) ? $data['nis'] : explode('@', $data['email'])[0]);

                // Ensure unique username if collision might occur with email prefix?
                // For now assuming safe inputs or DB will throw error which transaction catches.

                $userData = [
                    'email' => $data['email'],
                    'username' => $username,
                    'password' => $data['password'] ?? '123456', // Default password
                    'role' => 'student',
                    'status' => 1
                ];

                $userId = $userModel->create($userData);
                if (!$userId) {
                    throw new \Exception("Failed to create user account");
                }

                // 2. Create Student linked to User ID
                $data['id'] = $userId;

                // Remove fields not in student table if necessary, but ActiveRecord filters fillable.
                // Ensure fields like 'password' are not passed if not in fillable.

                $this->model->create($data);
                return [
                    'success' => true,
                    'item' => $this->model->find($userId),
                    'message' => 'Student created successfully'
                ];
            });
        } catch (\Exception $e) {
            return $this->response->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Batch create students from import
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
                $userRows = [];
                $usernames = [];

                // 1. Prepare User Data & Pre-Map Student Data
                $mappedItems = [];

                $currUserId = Auth::userId();
                $nowUnix = time();
                $nowDateTime = date('Y-m-d H:i:s');

                foreach ($items as $item) {
                    // Basic validation
                    if (empty($item['name']) || empty($item['nisn']) || empty($item['nis']) || empty($item['email'])) {
                        continue; // Skip invalid rows or throw? Let's skip
                    }

                    $username = !empty($item['nisn']) ? $item['nisn'] : (!empty($item['nis']) ? $item['nis'] : explode('@', $item['email'])[0]);

                    // Avoid duplicate usernames in the batch itself
                    if (in_array($username, $usernames)) continue;

                    $usernames[] = $username;

                    $userRows[] = [
                        'email' => $item['email'],
                        'username' => $username,
                        'password' => password_hash($item['password'] ?? '123456', PASSWORD_BCRYPT),
                        'role' => 'student',
                        'status' => 'active',
                        'created_at' => $nowUnix,
                        'updated_at' => $nowUnix,
                        // 'created_by' => $currUserId, // users table might not have created_by on some schemas, but let's check
                    ];

                    // Map English input keys to Indonesian DB columns for Student
                    $mappedItems[$username] = [
                        'name' => $item['name'],
                        'nis' => $item['nis'] ?? null,
                        'nisn' => $item['nisn'] ?? null,
                        'jenis_kelamin' => $item['gender'] ?? null,
                        'tempat_lahir' => $item['place_of_birth'] ?? null,
                        'tanggal_lahir' => $item['date_of_birth'] ?? null,
                        'agama' => $item['religion'] ?? null,
                        'no_telp' => $item['phone'] ?? null,
                        'alamat' => $item['address'] ?? null,
                        'email' => $item['email'],
                        'father_name' => $item['father_name'] ?? null,
                        'mother_name' => $item['mother_name'] ?? null,
                        'status' => $item['status'] ?? 1,
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
                // Since we don't know IDs, query back by usernames
                $users = $userModel::findQuery()
                    ->select(['id', 'username'])
                    ->where(['username' => $usernames])
                    ->all();

                $userMap = []; // username => id
                foreach ($users as $u) {
                    $userMap[$u['username']] = $u['id'];
                }

                // 4. Bind IDs to Student Data
                $studentRows = [];
                foreach ($mappedItems as $username => $sData) {
                    if (isset($userMap[$username])) {
                        $sData['id'] = $userMap[$username];
                        $studentRows[] = $sData;
                    }
                }

                if (empty($studentRows)) {
                    throw new \Exception("Failed to map users to students");
                }

                // 5. Batch Insert Students
                if (!$this->model->batchInsert($studentRows)) {
                    throw new \Exception("Failed to batch insert students");
                }

                return [
                    'success' => true,
                    'count' => count($studentRows),
                    'message' => count($studentRows) . ' students imported successfully.'
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
        $nises = $this->request->input('nises', []);
        $nisns = $this->request->input('nisns', []);

        $result = [
            'existing_emails' => [],
            'existing_nises' => [],
            'existing_nisns' => []
        ];

        if (!empty($emails)) {
            $result['existing_emails'] = $this->model::findQuery()
                ->where(['email' => $emails])
                ->select('email')
                ->column();
        }

        if (!empty($nises)) {
            $result['existing_nises'] = $this->model::findQuery()
                ->where(['nis' => $nises])
                ->select('nis')
                ->column();
        }

        if (!empty($nisns)) {
            $result['existing_nisns'] = $this->model::findQuery()
                ->where(['nisn' => $nisns])
                ->select('nisn')
                ->column();
        }

        return array_merge(['success' => true], $result);
    }

    /**
     * Delete student and associated user
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $student = $this->model->find($id);

        if (!$student) {
            throw new \Exception('Student not found', 404);
        }

        try {
            return Database::transaction(function () use ($id) {
                try {
                    // Attempt to delete Student record first
                    $this->model->delete($id);

                    // If successful, attempt to delete User record
                    $userModel = new \App\Models\User();
                    $userModel->delete($id);

                    return $this->noContent();
                } catch (\PDOException $e) {
                    // If deletion fails (likely due to foreign key constraint)
                    // Set status to 0 for Student and status to inactive for User
                    $this->model->update($id, ['status' => 0]);

                    $userModel = new \App\Models\User();
                    $userModel->update($id, ['status' => 0]);

                    return [
                        'success' => true,
                        'message' => 'Student has relations and was deactivated instead of deleted.',
                        'item' => $this->model->find($id)
                    ];
                }
            });
        } catch (\Exception $e) {
            return $this->response->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all inactive students
     * GET /student/not-active-student
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
