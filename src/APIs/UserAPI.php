<?php

use App\Helpers\Database;
use App\Models\UserModel;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * User API endpoint.
 *
 * This file acts as a small API controller for frontend validation requests.
 * It is used by client-side JavaScript to validate usernames and emails
 * before form submission, keeping validation logic in one place.
 */
class UserAPI
{
    private UserModel $userModel;

    /**
     * @param UserModel $userModel Model responsible for user database queries.
     */
    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Handle the incoming request and return JSON.
     * Supports checking username availability and email availability.
     */
    public function handleRequest(): void
    {
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_REQUEST;
        }

        if (isset($payload['username'])) {
            echo json_encode($this->checkUsername($payload['username']));
            return;
        }

        if (isset($payload['email'])) {
            echo json_encode($this->checkEmail($payload['email']));
            return;
        }

        echo json_encode([
            'valid' => false,
            'available' => false,
            'errors' => ['Missing username or email.']
        ]);
    }

    /**
     * Validate username rules and check availability.
     *
     * @param string $username
     * @return array Response payload for frontend validation.
     */
    public function checkUsername(string $username): array
    {
        $username = trim($username);
        $errors = [];

        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9_.]*$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, underscores and dots, and must start with a letter.';
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'available' => false,
                'errors' => $errors
            ];
        }

        return [
            'valid' => true,
            'available' => $this->userModel->checkUsernameAvailability($username),
            'errors' => []
        ];
    }

    /**
     * Validate email rules and check availability.
     *
     * @param string $email
     * @return array Response payload for frontend validation.
     */
    public function checkEmail(string $email): array
    {
        $email = trim($email);
        $errors = [];

        if ($email === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is not valid.';
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'available' => false,
                'errors' => $errors
            ];
        }

        return [
            'valid' => true,
            'available' => $this->userModel->checkEmailAvailability($email),
            'errors' => []
        ];
    }
}

// Instantiate the API and process the request.
$database = Database::getInstance();
$userModel = new UserModel($database);
$api = new UserAPI($userModel);
$api->handleRequest();
