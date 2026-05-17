<?php
/**
 * Application Constants
 *
 * This file defines constants used throughout the application.
 * Following the principle: "If hidden/hardcoded → use constants"
 */

// User Roles - Magic strings converted to constants
const ROLE_STUDENT = 'student';
const ROLE_FACULTY = 'faculty';

// Database Table Names - Magic strings converted to constants
const TABLE_USERS = 'users';
const TABLE_CLASSES = 'classes';
const TABLE_ACTIVITIES = 'activities';
const TABLE_ENROLLMENTS = 'enrollments';
const TABLE_SUBMISSIONS = 'submissions';

// Default Values - Magic numbers/strings converted to constants
const DEFAULT_USER_ROLE = ROLE_STUDENT;
const DEFAULT_DB_HOST = 'localhost';
const DEFAULT_DB_PORT = 3306;
const DEFAULT_DB_NAME = 'lms_db';
const DEFAULT_DB_CHARSET = 'utf8mb4';
const DEFAULT_DB_DRIVER = 'mysql';

// Session Keys - Magic strings converted to constants
const SESSION_USER = 'user';
const SESSION_FLASH_ERROR = 'flash_error';
const SESSION_FLASH_SUCCESS = 'flash_success';
const SESSION_FLASH_INPUT = 'flash_input';

// File Paths - Magic strings converted to constants
const PATH_CONFIG = __DIR__ . '/../config/config.php';
const PATH_ENV = __DIR__ . '/../.env';

// Error Messages - Magic strings converted to constants
const ERR_INVALID_ACTIVITY_ID = 'Invalid activity ID.';
const ERR_ACTIVITY_NOT_FOUND = 'Activity not found or access denied.';
const ERR_ALREADY_SUBMITTED = 'You have already submitted this activity.';
const ERR_DB_CONNECTION_FAILED = 'Database connection failed. Please try again later.';
const ERR_INVALID_INPUT = 'Invalid input.';
const ERR_ACCESS_DENIED = 'Access denied.';
const ERR_CLASS_NOT_FOUND = 'Class not found or access denied.';
const ERR_ALREADY_JOINED = 'Already joined this class.';
const ERR_ERROR_JOINING = 'Error joining class: ';
const ERR_ERROR_LEAVING = 'Error leaving class: ';
const ERR_ERROR_GRADING = 'Error grading submission.';
const ERR_ERROR_CREATING_ACTIVITY = 'Error creating activity.';
const ERR_ERROR_CREATING_CLASS = 'Error creating class: ';

// Success Messages - Magic strings converted to constants
const MSG_CLASS_DELETED = 'Class deleted successfully.';
const MSG_LEFT_CLASS = 'Successfully left the class.';

// PDO Attributes - Magic numbers converted to constants
const PDO_ATTR_ERRMODE = PDO::ERRMODE_EXCEPTION;
const PDO_ATTR_FETCH_MODE = PDO::FETCH_ASSOC;
const PDO_ATTR_EMULATE_PREPARES = false;

