<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare('SELECT user_id, full_name, password, user_type FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $full_name, $hash, $user_type);
        if ($stmt->fetch() && password_verify($password, $hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['user_type'] = $user_type;
            redirect('dashboard.php');
        }
        $stmt->close();
        $error = 'Email or password is incorrect.';
    } else {
        $error = 'Please enter both email and password.';
    }
}
include_once 'includes/header.php';
?>
<section class="auth-card">
    <h1>Login</h1>
    <?php if ($error): ?>
        <?php echo flashMessage($error, 'danger'); ?>
    <?php endif; ?>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button class="btn btn-primary" type="submit">Sign in</button>
    </form>
    <p>New here? <a href="register.php">Register now</a>.</p>
</section>
<?php include_once 'includes/footer.php'; ?>