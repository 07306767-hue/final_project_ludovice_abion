<?php
require_once __DIR__ . '/../../src/Helpers/_init.php';

$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['user']['role'] = $user['role'] ?? 'student';
        redirect('dashboard.php');
    }

    $error = 'Invalid email or password!';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="../assets/CSS/login.css">
</head>
<body>

<div class="header">
    <div class="left">
        <img src="../assets/images/logo.png" class="logo">
        <h1 class="brand">A.I Academy</h1>
    </div>
    <div class="right">
        <a href="../index.php" class="back-btn">Back</a>
    </div>
</div>

<div class="main">

<div class="login-box">

<h2>Login</h2>

<?php if ($error != ""): ?>
    <p style="color:red;"><?= $error; ?></p>
<?php endif; ?>

<form method="POST">

    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button name="login">Login</button>
    
<div class="login-options">
    <a href="forgot.php">Forgot Password?</a>
</div>

</form>

<p>
Don't have an account?
<a href="register.php">Create Account</a>
</p>

</div>

</div>

</body>
</html>