<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" href="../assets/CSS/register.css">
</head>
<body>

<?php


require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\UserController;


$conn = new mysqli("localhost", "root", "", "lms_db");

$serverWarnings = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'password' => '',
    'confirm' => '',
    'role' => ''
];

$postValues = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'role' => ''
];

$showSuccessPopup = false;

if (isset($_POST['register'])) {

    $controller = new UserController($conn);

    $postValues['firstname'] = $_POST['firstname'] ?? '';
    $postValues['lastname'] = $_POST['lastname'] ?? '';
    $postValues['email'] = $_POST['email'] ?? '';
    $postValues['role'] = $_POST['role'] ?? '';

    $result = $controller->register($_POST);

    if (!$result['status']) {
        $serverWarnings = array_merge($serverWarnings, $result['errors']);
    } else {
        $showSuccessPopup = true;
    }
}

if ($showSuccessPopup) {
    echo "
    <div id='popup'>
        <div class='popup-box'>
            <div class='spinner'></div>
            <h3>Registration Successful!</h3>
            <p>Account created successfully</p>
            <p class='redirect-text'>Redirecting to login in <span id='countdown'>3</span> seconds...</p>
            <div class='progress-bar'>
                <div class='progress-fill'></div>
            </div>
        </div>
    </div>

    <script>
    let countdown = 3;
    const countdownElement = document.getElementById('countdown');
    
    const countdownInterval = setInterval(() => {
        countdown--;
        if (countdownElement) {
            countdownElement.textContent = countdown;
        }
        if (countdown <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
    
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 3000);
    </script>
    ";
}
?>

<div class="header">
    <div class="left">
        <img src="../assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>
</div>

<div class="main">

<div class="register-box">

<h2>Register</h2>

<form method="POST" id="registerForm" novalidate>
    <div class="form-group">
        <input type="text" id="firstname" name="firstname" placeholder="First Name" value="<?= htmlspecialchars($postValues['firstname']) ?>" required>
        <div class="field-warning" id="firstnameWarning"><?= htmlspecialchars($serverWarnings['firstname']) ?></div>
    </div>

    <div class="form-group">
        <input type="text" id="lastname" name="lastname" placeholder="Last Name" value="<?= htmlspecialchars($postValues['lastname']) ?>" required>
        <div class="field-warning" id="lastnameWarning"><?= htmlspecialchars($serverWarnings['lastname']) ?></div>
    </div>

    <div class="form-group">
        <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($postValues['email']) ?>" required>
        <div class="field-warning" id="emailWarning"><?= htmlspecialchars($serverWarnings['email']) ?></div>
    </div>

    <div class="form-group">
        <input type="password" id="password" name="password" placeholder="Password" required>
        <div class="field-warning" id="passwordWarning"><?= htmlspecialchars($serverWarnings['password']) ?></div>
    </div>

    <div class="form-group">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        <div class="field-warning" id="confirmWarning"><?= htmlspecialchars($serverWarnings['confirm']) ?></div>
    </div>

    <div class="role-box">
        <p>Select Role:</p>
        <div class="role-options">
            <label class="role-option">
                <input type="radio" id="role_student" name="role" value="student" required <?= $postValues['role'] === 'student' ? 'checked' : '' ?>>
                Student
            </label>
            <label class="role-option">
                <input type="radio" id="role_faculty" name="role" value="faculty" <?= $postValues['role'] === 'faculty' ? 'checked' : '' ?>>
                Faculty
            </label>
        </div>
        <div class="field-warning" id="roleWarning"><?= htmlspecialchars($serverWarnings['role']) ?></div>
    </div>

    <button id="registerBtn" name="register" disabled>Register</button>
</form>


<p>
Already have an account?
<a href="login.php">Login</a>
</p>

</div>
</div>

<script>
const form = document.getElementById('registerForm');
const registerBtn = document.getElementById('registerBtn');
const firstnameInput = document.getElementById('firstname');
const lastnameInput = document.getElementById('lastname');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm_password');
const roleInputs = document.querySelectorAll('input[name="role"]');
const firstnameWarning = document.getElementById('firstnameWarning');
const lastnameWarning = document.getElementById('lastnameWarning');
const emailWarning = document.getElementById('emailWarning');
const passwordWarning = document.getElementById('passwordWarning');
const confirmWarning = document.getElementById('confirmWarning');
const roleWarning = document.getElementById('roleWarning');

const serverWarnings = {
    firstname: <?= json_encode($serverWarnings['firstname']) ?>,
    lastname: <?= json_encode($serverWarnings['lastname']) ?>,
    email: <?= json_encode($serverWarnings['email']) ?>,
    password: <?= json_encode($serverWarnings['password']) ?>,
    confirm: <?= json_encode($serverWarnings['confirm']) ?>,
    role: <?= json_encode($serverWarnings['role']) ?>
};

const previousValues = {
    firstname: <?= json_encode($postValues['firstname']) ?>,
    lastname: <?= json_encode($postValues['lastname']) ?>,
    email: <?= json_encode($postValues['email']) ?>,
    role: <?= json_encode($postValues['role']) ?>
};

const touched = {
    firstname: false,
    lastname: false,
    email: false,
    password: false,
    confirm: false,
    role: false
};

function validateFirstname(value) {
    if (value.trim().length < 2) {
        return 'First name must be at least 2 characters.';
    }
    if (/\s/.test(value)) {
        return 'First name cannot contain spaces.';
    }
    return '';
}

function validateLastname(value) {
    if (value.trim().length < 2) {
        return 'Last name must be at least 2 characters.';
    }
    if (/\s/.test(value)) {
        return 'Last name cannot contain spaces.';
    }
    return '';
}

function validateEmail(value) {
    if (!value.trim()) {
        return 'Email is required.';
    }
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(value)) {
        return 'Enter a valid email address.';
    }
    return '';
}

function validatePassword(value) {
    if (value.length < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!/[A-Z]/.test(value)) {
        return 'Password must contain at least one uppercase letter.';
    }
    return '';
}

function validateConfirmPassword(password, confirm) {
    if (confirm.length === 0) {
        return 'Please confirm your password.';
    }
    if (password !== confirm) {
        return 'Passwords do not match.';
    }
    return '';
}

function getSelectedRole() {
    for (const input of roleInputs) {
        if (input.checked) {
            return input.value;
        }
    }
    return '';
}

function updateFormValidation() {
    // Clear server warnings when user starts typing
    if (firstnameInput.value !== previousValues.firstname) {
        serverWarnings.firstname = '';
    }
    if (lastnameInput.value !== previousValues.lastname) {
        serverWarnings.lastname = '';
    }
    if (emailInput.value !== previousValues.email) {
        serverWarnings.email = '';
    }
    if (getSelectedRole() !== previousValues.role) {
        serverWarnings.role = '';
    }

    // Only show errors if field is touched or has server-side errors
    const firstnameText = (touched.firstname || serverWarnings.firstname) ? (validateFirstname(firstnameInput.value) || serverWarnings.firstname) : '';
    const lastnameText = (touched.lastname || serverWarnings.lastname) ? (validateLastname(lastnameInput.value) || serverWarnings.lastname) : '';
    const emailText = (touched.email || serverWarnings.email) ? (validateEmail(emailInput.value) || serverWarnings.email) : '';
    const passwordText = (touched.password || serverWarnings.password) ? (validatePassword(passwordInput.value) || serverWarnings.password) : '';
    const confirmText = (touched.confirm || serverWarnings.confirm) ? (validateConfirmPassword(passwordInput.value, confirmInput.value) || serverWarnings.confirm) : '';
    const roleText = (touched.role || serverWarnings.role) ? (getSelectedRole() ? '' : (serverWarnings.role || 'Please select a role.')) : '';

    // Update warning messages
    firstnameWarning.textContent = firstnameText;
    lastnameWarning.textContent = lastnameText;
    emailWarning.textContent = emailText;
    passwordWarning.textContent = passwordText;
    confirmWarning.textContent = confirmText;
    roleWarning.textContent = roleText;

    // Update field styling based on errors
    updateFieldStyle('firstname', firstnameText);
    updateFieldStyle('lastname', lastnameText);
    updateFieldStyle('email', emailText);
    updateFieldStyle('password', passwordText);
    updateFieldStyle('confirm_password', confirmText);
    updateRoleFieldStyle(roleText);

    // Disable submit button if there are errors
    const hasErrors = firstnameText || lastnameText || emailText || passwordText || confirmText || roleText;
    registerBtn.disabled = !!hasErrors;
}

function updateFieldStyle(fieldId, errorText) {
    const field = document.getElementById(fieldId);
    if (errorText) {
        field.classList.add('input-error');
    } else {
        field.classList.remove('input-error');
    }
}

function updateRoleFieldStyle(errorText) {
    const roleContainer = document.querySelector('.role-options');
    if (errorText) {
        roleContainer.classList.add('role-error');
    } else {
        roleContainer.classList.remove('role-error');
    }
}

form.addEventListener('input', updateFormValidation);
form.addEventListener('change', updateFormValidation);

// Mark fields as touched on blur
firstnameInput.addEventListener('blur', () => {
    touched.firstname = true;
    updateFormValidation();
});

lastnameInput.addEventListener('blur', () => {
    touched.lastname = true;
    updateFormValidation();
});

emailInput.addEventListener('blur', () => {
    touched.email = true;
    updateFormValidation();
});

passwordInput.addEventListener('blur', () => {
    touched.password = true;
    updateFormValidation();
});

confirmInput.addEventListener('blur', () => {
    touched.confirm = true;
    updateFormValidation();
});

roleInputs.forEach(input => {
    input.addEventListener('change', () => {
        touched.role = true;
        updateFormValidation();
    });
});

// Initialize - only show server-side errors on page load
updateFormValidation();

// If there are server-side errors, mark those fields as touched
if (serverWarnings.firstname) touched.firstname = true;
if (serverWarnings.lastname) touched.lastname = true;
if (serverWarnings.email) touched.email = true;
if (serverWarnings.password) touched.password = true;
if (serverWarnings.confirm) touched.confirm = true;
if (serverWarnings.role) touched.role = true;

updateFormValidation();
</script>
</body>
</html>