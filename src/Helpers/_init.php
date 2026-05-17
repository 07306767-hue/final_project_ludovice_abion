<?php

/**
 * Page initializer for public/pages.
 *
 * This file starts the session, loads database config,
 * and provides small helper functions for authentication
 * and redirects used by pages in public/pages.
 *
 * Magic Strings → Constants: Role checking uses constants instead of hardcoded strings
 */
session_start();
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/../../src/Helpers/Constants.php"; // Load constants
\App\Helpers\EnvParser::load(__DIR__ . "/../../.env");
require_once __DIR__ . "/../../config/config.php";

function requireLogin(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: /Abion-Ludovice/public/pages/login.php');
        exit();
    }
}

function requireRole(string $role): void
{
    requireLogin();

    $userRole = $_SESSION['user']['role'] ?? null;

    if (!$userRole) {
        die("No role found in session.");
    }

    if ($userRole !== $role) {
        die("ACCESS DENIED: role = $userRole, required = $role");
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

function flashError(string $message): void
{
    $_SESSION['flash_error'] = $message;
}

function flashSuccess(string $message): void
{
    $_SESSION['flash_success'] = $message;
}

function flashInput(string $key, string $value): void
{
    $_SESSION['flash_input'][$key] = $value;
}

function getFlashInput(string $key): string
{
    $value = $_SESSION['flash_input'][$key] ?? '';
    unset($_SESSION['flash_input'][$key]);
    return $value;
}

function getFlashError(): string
{
    $message = $_SESSION['flash_error'] ?? '';
    unset($_SESSION['flash_error']);
    return $message;
}

function getFlashSuccess(): string
{
    $message = $_SESSION['flash_success'] ?? '';
    unset($_SESSION['flash_success']);
    return $message;
}
