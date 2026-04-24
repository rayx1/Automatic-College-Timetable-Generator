<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    $user = current_user();
    redirect('/automatic-college-timetable-generator/' . $user['role'] . '/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) post('email'));
    $password = (string) post('password');

    $user = fetch_one('SELECT * FROM users WHERE email = ?', [$email]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        redirect('/automatic-college-timetable-generator/' . $user['role'] . '/dashboard.php');
    }

    flash('danger', 'Invalid email or password.');
    redirect('/automatic-college-timetable-generator/auth/login.php');
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card p-4">
            <h1 class="h4 mb-3">Login</h1>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Login</button>
            </form>
            <p class="small-muted mt-3 mb-0">Use the demo credentials from the README or `database.sql` seed data.</p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

