<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) post('name'));
    $email = trim((string) post('email'));
    $departmentId = (int) post('department_id');
    $password = (string) post('password');
    execute_query('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)', [$name, $email, password_hash($password, PASSWORD_BCRYPT), 'faculty']);
    $userId = (int) db()->lastInsertId();
    execute_query('INSERT INTO faculty (user_id, name, department_id) VALUES (?, ?, ?)', [$userId, $name, $departmentId]);
    flash('success', 'Faculty saved.');
    redirect('/automatic-college-timetable-generator/admin/faculty.php');
}

$departments = get_departments();
$rows = get_faculty();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Faculty</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select" required>
                        <?php foreach ($departments as $department): ?><option value="<?= e((string) $department['id']); ?>"><?= e($department['name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Faculty</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string) $row['id']); ?></td><td><?= e($row['name']); ?></td><td><?= e($row['email']); ?></td><td><?= e($row['department_name']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

