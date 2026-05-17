<?php

namespace Validators;

class UserValidator
{
    public static function validateRegistration($conn, $data)
    {
        $errors = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'password' => '',
            'confirm' => '',
            'role' => ''
        ];

        $firstname = trim($data['firstname'] ?? '');
        $lastname = trim($data['lastname'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        $role = $data['role'] ?? '';

        // First name rules
        if (strlen($firstname) < 2) {
            $errors['firstname'] = 'First name must be at least 2 characters.';
        } elseif (preg_match('/\s/', $firstname)) {
            $errors['firstname'] = 'First name cannot contain spaces.';
        }

        // Last name rules
        if (strlen($lastname) < 2) {
            $errors['lastname'] = 'Last name must be at least 2 characters.';
        } elseif (preg_match('/\s/', $lastname)) {
            $errors['lastname'] = 'Last name cannot contain spaces.';
        }

        // Email rules
        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif (self::emailExists($conn, $email)) {
            $errors['email'] = 'Email is already taken.';
        }

        // Password match
        if ($password !== $confirm) {
            $errors['confirm'] = 'Passwords do not match.';
        }

        // Password rules
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter.';
        }

        if ($role === '') {
            $errors['role'] = 'Please select a role.';
        }

        return array_filter($errors);
    }

    public static function emailExists($conn, $email)
    {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public static function usernameExists($conn, $firstname, $lastname)
    {
        $stmt = $conn->prepare("SELECT id FROM users WHERE firstname = ? AND lastname = ?");
        $stmt->bind_param("ss", $firstname, $lastname);
        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows > 0;
    }

    public static function isValidPassword($password)
    {
        // at least 8 chars
        if (strlen($password) < 8) {
            return false;
        }

        // must contain at least 1 uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        return true;
    }

}

