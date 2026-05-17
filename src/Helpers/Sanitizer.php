<?php
namespace App\Helpers;

class Sanitizer {
    
    /**
     * Sanitize username - allow only letters, numbers, underscores, dots
     */
    public function sanitizeUsername($username) {
        if ($username === null) return '';
        
        // Trim whitespace
        $sanitized = trim($username);
        
        // Remove HTML tags
        $sanitized = strip_tags($sanitized);
        
        // Convert special characters to HTML entities
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        // Allow only letters, numbers, underscores, and dots
        $sanitized = preg_replace('/[^a-zA-Z0-9_.]/', '', $sanitized);
        
        return $sanitized;
    }
    
    /**
     * Sanitize email
     */
    public function sanitizeEmail($email) {
        if ($email === null) return '';
        
        $sanitized = trim($email);
        $sanitized = strip_tags($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        $sanitized = filter_var($sanitized, FILTER_SANITIZE_EMAIL);
        
        return $sanitized;
    }
    
    /**
     * Sanitize password (minimal sanitization as passwords should be hashed)
     */
    public function sanitizePassword($password) {
        if ($password === null) return '';
        
        // Just trim whitespace, don't modify password content
        return trim($password);
    }
    
    /**
     * Generic sanitize for string inputs
     */
    public function sanitizeString($input) {
        if ($input === null) return '';
        
        $sanitized = trim($input);
        $sanitized = strip_tags($sanitized);
        $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
        
        return $sanitized;
    }
    
    /**
     * Sanitize array of inputs based on field types
     */
    public function sanitizeArray($data, $fieldTypes = []) {
        $sanitized = [];
        
        foreach ($data as $field => $value) {
            if (isset($fieldTypes[$field])) {
                switch ($fieldTypes[$field]) {
                    case 'username':
                        $sanitized[$field] = $this->sanitizeUsername($value);
                        break;
                    case 'email':
                        $sanitized[$field] = $this->sanitizeEmail($value);
                        break;
                    case 'password':
                        $sanitized[$field] = $this->sanitizePassword($value);
                        break;
                    default:
                        $sanitized[$field] = $this->sanitizeString($value);
                }
            } else {
                $sanitized[$field] = $this->sanitizeString($value);
            }
        }
        
        return $sanitized;
    }
}
?>