<?php
session_start();
include_once 'includes/db.php';
include_once 'includes/functions.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $user_type = in_array($_POST['user_type'] ?? 'buyer', ['buyer', 'seller']) ? $_POST['user_type'] : 'buyer';

    if ($full_name && $email && $password) {
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'That email is already registered. Please login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare('INSERT INTO users (full_name, email, password, user_type) VALUES (?, ?, ?, ?)');
            $insert->bind_param('ssss', $full_name, $email, $hash, $user_type);
            if ($insert->execute()) {
                $success = 'Registration successful. You can now log in.';
            } else {
                $error = 'Could not register. Please try again.';
            }
            $insert->close();
        }
        $stmt->close();
    } else {
        $error = 'Please complete all fields.';
    }
}
include_once 'includes/header.php';
?>
<section class="auth-card">
    <h1>Register</h1>
    <?php if ($error): ?>
        <?php echo flashMessage($error, 'danger'); ?>
    <?php elseif ($success): ?>
        <?php echo flashMessage($success, 'success'); ?>
    <?php endif; ?>
    <form method="POST">
        <label>Full name</label>
        <input type="text" name="full_name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Account type</label>
        <select name="user_type">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
        </select>
        <button class="btn btn-primary" type="submit">Create account</button>
    </form>
    <p>Already have an account? <a href="login.php">Sign in</a>.</p>
</section>
<?php include_once 'includes/footer.php'; ?>