<?php

namespace GoFrame\Controllers;

use GoFrame\Core\Helpers\Logger;
use GoFrame\Core\Helpers\Output;
use GoFrame\Core\Helpers\Validation;
use GoFrame\Models\User;
use PDOException;
use Ramsey\Uuid\Uuid;

/**
 * Class UserController
 *
 * Handles CRUD operations for User model with validation, error handling and structured output.
 *
 * Example usage:
 * ```php
 * $controller = new UserController();
 * $controller->getAll();           // Get all users
 * $controller->findOne('id');    // Get user by ID
 * $controller->create(['name' => 'New name']); // Create new user
 * $controller->update('id', ['name' => 'New Name']); // Update user
 * $controller->delete('id');     // Delete user
 * ```
 *
 * @package GoFrame\Controllers
 */
class UserController {
    protected User $userModel;
    private Output $output;
    private Validation $validation;
    private Logger $logger;

    /**
     * UserController constructor.
     *
     * Initializes dependencies and the user model.
     */
    public function __construct() {
        $this->userModel = new User();
        $this->output = new Output();
        $this->validation = new Validation();
        $this->logger = new Logger();
    }

    /**
     * Fetches all users with pagination support.
     * For pagination or ordering, consult the documentation for BaseModel.
     *
     * Returns a JSON response containing all users, total pages and status information.
     *
     * @return void
     */
    public function getAll() {
        try {            
            $users = $this->userModel->findAll();

            if (empty($users['data'])) {
                $this->output->buildOutput([
                    'status' => 'empty',
                    'status_code' => '200'
                ]);
            }

            $this->output->buildOutput([
                'status' => 'success',
                'status_code' => '200',
                'data' => $users['data'],
                'total_pages' => $users['pages']
            ]);
        } catch (PDOException $e) {
            $error_id = Uuid::uuid4();
            $this->logger->newLog('user.log', $error_id . ' | ' . $e->getMessage(), 'ERROR');

            $this->output->buildOutput([
                'status' => 'server_error',
                'status_code' => '500',
                'error_id' => $error_id
            ]);
        }
    }

    /**
     * Finds a single user by column and value.
     *
     * @param mixed $value Value to search by (typically the user ID).
     * @return void
     */
    public function findOne($value) {
        try {
            $user = $this->userModel->findOne('id', $value);

            if (empty($user)) {
                $this->output->buildOutput([
                    'status' => 'not_found',
                    'status_code' => '404'
                ]);
            }

            $this->output->buildOutput([
                'status' => 'success',
                'status_code' => '200',
                'data' => $user
            ]);
        } catch (PDOException $e) {
            $error_id = Uuid::uuid4();
            $this->logger->newLog('user.log', $error_id . ' | ' . $e->getMessage(), 'ERROR');

            $this->output->buildOutput([
                'status' => 'server_error',
                'status_code' => '500',
                'error_id' => $error_id
            ]);
        }
    }

    /**
     * Deletes a user by ID.
     *
     * Handles foreign key constraint errors and logs unexpected exceptions.
     *
     * @param mixed $value User ID to delete.
     * @return void
     */
    public function delete($id) {
        try {
            $users = $this->userModel->findOne('id', $id);

            if (empty($users)) {
                $this->output->buildOutput([
                    'status' => 'not_found',
                    'status_code' => '404'
                ]);
            }

            $this->userModel->delete('id', $id);

            $this->output->buildOutput([
                'status' => 'success',
                'status_code' => '200'
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
                $this->output->buildOutput([
                    'status' => 'bad_request',
                    'status_code' => '400'
                ]);
                return;
            }

            $error_id = Uuid::uuid4();
            $this->logger->newLog('user.log', $error_id . ' | ' . $e->getMessage(), 'ERROR');

            $this->output->buildOutput([
                'status' => 'server_error',
                'status_code' => '500',
                'error_id' => $error_id
            ]);
        }
    }

    /**
     * Creates a new user record.
     *
     * Validates required fields before inserting. Handles duplicate key violations and exceptions.
     *
     * @param array $data User data to insert.
     * @return void
     */
    public function create($data) {
        try {
           
            $errors = $this->validation->validateFields($data, $this->userModel->getColumns());

            if (!empty($errors)) {
                $this->output->buildOutput([
                    'status' => 'bad_request',
                    'status_code' => '400',
                    'error' => $errors
                ]);
                return;
            }

            $this->userModel->insert($data);

            $this->output->buildOutput([
                'status' => 'success',
                'status_code' => '200'
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->output->buildOutput([
                    'status' => 'conflict',
                    'status_code' => '409'
                ]);
                return;
            }

            $error_id = Uuid::uuid4();
            $this->logger->newLog('user.log', $error_id . ' | ' . $e->getMessage(), 'ERROR');

            $this->output->buildOutput([
                'status' => 'server_error',
                'status_code' => '500',
                'error_id' => $error_id
            ]);
        }
    }

    /**
     * Updates an existing user record.
     *
     * Validates fields before updating. Rejects unknown fields.
     *
     * @param mixed $id Identifier for the user (usually UUID).
     * @param array $data Associative array of fields to update.
     * @return void
     */
    public function update($id, $data) {
        try {             
            $errors = $this->validation->validateFields($data, $this->userModel->getColumns());

            if (!empty($errors['incorrect_fields'])) {
                $this->output->buildOutput([
                    'status' => 'bad_request',
                    'status_code' => '400',
                    'error' => $errors
                ]);
                return;
            }

            $busca = $this->userModel->findOne('id', $id);
            if (empty($busca)) {
                $this->output->buildOutput([
                    'status' => 'not_found',
                    'status_code' => '404'
                ]);
            }

            $this->userModel->update('id', $id, $data);

            $this->output->buildOutput([
                'status' => 'success',
                'status_code' => '200'
            ]);
        } catch (PDOException $e) {
            $error_id = Uuid::uuid4();
            $this->logger->newLog('user.log', $error_id . ' | ' . $e->getMessage(), 'ERROR');

            $this->output->buildOutput([
                'status' => 'server_error',
                'status_code' => '500',
                'error_id' => $error_id
            ]);
        }
    }
}
