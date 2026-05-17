<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
session_start();
require_once "../vendor/autoload.php";

use App\Controllers\UserController;
use App\Models\UserModel;
use App\Helpers\Database;   
use App\Helpers\EnvParser;

EnvParser::load(__DIR__ . "/../.env");

$database = Database::getInstance();
$model = new UserModel($database);
$controller = new UserController($model);
$message = '';
$messageType = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $data = [
        "username" => $_POST["username"],
        "email" => $_POST["email"] ?? '',
        "password" => $_POST["password"] ?? '',
        "confirm_pass" => $_POST["confirm_pass"] ?? ''
    ];
    
    $result = $controller->validateAndProcessRegistration($data);
    
    if ($result['success']) {
        $_SESSION['success'] = "User registered successfully!";
        $_SESSION['user_data'] = $result['data'];
    } else {
        $_SESSION['error'] = implode('<br>', $result['errors']);
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get session messages
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
    unset($_SESSION['user_data']);
} elseif (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
}

// Get all users for display
$users = $model->selectAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="assets/CSS/index.css">
</head>
<body>

<div class="header">
    <div class="left">
        <img src="assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>

    <div class="right">
        <a href="pages/login.php" class="btn">Login</a>
        <a href="pages/register.php" class="btn">Register</a>
    </div>
</div>

<div class="main">
    <h1>Welcome to A.I Academy</h1>
</div>
<div class="container">

    <div class="hero">
        <h1>Join Class now</h1>
        <p>Learn, track progress, and manage your courses.</p>
        <a href="pages/login.php" class="start-btn">Get Started</a>
    </div>

    <div class="courses">
        <h2>Available Subjects</h2>

        <div class="course-list">
            <div class="course-card">
                <h3>Web Development</h3>
                <p>HTML, CSS, and JavaScript basics.</p>
            </div>

            <div class="course-card">
                <h3>Database Systems</h3>
                <p>Learn SQL and database design.</p>
            </div>

            <div class="course-card">
                <h3>PHP Programming</h3>
                <p>Backend development with PHP.</p>
            </div>
        </div>
    </div>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const usernameStatus = document.getElementById('usernameStatus');
            const emailInput = document.getElementById('email');
            const emailStatus = document.getElementById('emailStatus');
            const submitBtn = document.getElementById('submitBtn');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_pass');
            let userTimeoutId;
            let emailTimeoutId;

            // Username availability check
            usernameInput.addEventListener('input', function() {
                const username = this.value;
                
                // Clear previous timeout
                if (userTimeoutId) {
                    clearTimeout(userTimeoutId);
                }

                // Client-side validation
                if (username.length === 0) {
                    usernameStatus.textContent = 'Username is required';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (username.length < 3) {
                    usernameStatus.textContent = 'Username must be at least 3 characters';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (!/^[a-zA-Z]/.test(username)) {
                    usernameStatus.textContent = 'Username must start with a letter';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (!/^[a-zA-Z][a-zA-Z0-9_.]*$/.test(username)) {
                    usernameStatus.textContent = 'Username can only contain letters, numbers, underscores and dots';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                // Show checking status
                usernameStatus.textContent = 'Checking availability...';
                usernameStatus.className = 'username-status checking';
                submitBtn.disabled = true;

                // Set timeout to avoid too many requests
                userTimeoutId = setTimeout(() => {
                    checkUsernameAvailability(username);
                }, 500);
            });

            // Username availability check
            function checkUsernameAvailability(username) {
                fetch('src/APIs/UserAPI.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username: username })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        usernameStatus.textContent = data.errors.join(', ');
                        usernameStatus.className = 'username-status unavailable';
                        submitBtn.disabled = true;
                    } else if (data.available) {
                        usernameStatus.textContent = '✓ Username is available!';
                        usernameStatus.className = 'username-status available';
                        submitBtn.disabled = false;
                    } else {
                        usernameStatus.textContent = '✗ Username is already taken';
                        usernameStatus.className = 'username-status unavailable';
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    usernameStatus.textContent = 'Error checking username. Please try again.';
                    usernameStatus.className = 'username-status unavailable';
                });
            }

            // Password match validation
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirm = confirmInput.value;
                
                if (confirm.length > 0) {
                    if (password !== confirm) {
                        confirmInput.setCustomValidity('Passwords do not match');
                    } else {
                        confirmInput.setCustomValidity('');
                    }
                }
            }

            passwordInput.addEventListener('change', checkPasswordMatch);
            confirmInput.addEventListener('keyup', checkPasswordMatch);

            // Form submission validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    alert('Please fix the username issues before submitting.');
                }
                
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        });
    </script> <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            const usernameStatus = document.getElementById('usernameStatus');
            const emailInput = document.getElementById('email');
            const emailStatus = document.getElementById('emailStatus');
            const submitBtn = document.getElementById('submitBtn');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_pass');
            let userTimeoutId;
            let emailTimeoutId;

            // Username availability check
            usernameInput.addEventListener('input', function() {
                const username = this.value;
                
                // Clear previous timeout
                if (userTimeoutId) {
                    clearTimeout(userTimeoutId);
                }

                // Client-side validation
                if (username.length === 0) {
                    usernameStatus.textContent = 'Username is required';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (username.length < 3) {
                    usernameStatus.textContent = 'Username must be at least 3 characters';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (!/^[a-zA-Z]/.test(username)) {
                    usernameStatus.textContent = 'Username must start with a letter';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                if (!/^[a-zA-Z][a-zA-Z0-9_.]*$/.test(username)) {
                    usernameStatus.textContent = 'Username can only contain letters, numbers, underscores and dots';
                    usernameStatus.className = 'username-status unavailable';
                    submitBtn.disabled = true;
                    return;
                }

                // Show checking status
                usernameStatus.textContent = 'Checking availability...';
                usernameStatus.className = 'username-status checking';
                submitBtn.disabled = true;

                // Set timeout to avoid too many requests
                userTimeoutId = setTimeout(() => {
                    checkUsernameAvailability(username);
                }, 500);
            });

            // Username availability check
            function checkUsernameAvailability(username) {
                fetch('src/APIs/UserAPI.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username: username })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        usernameStatus.textContent = data.errors.join(', ');
                        usernameStatus.className = 'username-status unavailable';
                        submitBtn.disabled = true;
                    } else if (data.available) {
                        usernameStatus.textContent = '✓ Username is available!';
                        usernameStatus.className = 'username-status available';
                        submitBtn.disabled = false;
                    } else {
                        usernameStatus.textContent = '✗ Username is already taken';
                        usernameStatus.className = 'username-status unavailable';
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    usernameStatus.textContent = 'Error checking username. Please try again.';
                    usernameStatus.className = 'username-status unavailable';
                });
            }

            // Password match validation
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirm = confirmInput.value;
                
                if (confirm.length > 0) {
                    if (password !== confirm) {
                        confirmInput.setCustomValidity('Passwords do not match');
                    } else {
                        confirmInput.setCustomValidity('');
                    }
                }
            }

            passwordInput.addEventListener('change', checkPasswordMatch);
            confirmInput.addEventListener('keyup', checkPasswordMatch);

            // Form submission validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                if (submitBtn.disabled) {
                    e.preventDefault();
                    alert('Please fix the username issues before submitting.');
                }
                
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        });
    </script>

</div>
</body>
</html>