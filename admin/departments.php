<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('INSERT INTO departments (name, code) VALUES (?, ?)', [trim((string) post('name')), trim((string) post('code'))]);
    flash('success', 'Department saved.');
    redirect('/automatic-college-timetable-generator/admin/departments.php');
}

$rows = get_departments();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Department</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Code</label><input name="code" class="form-control" required></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Departments</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>Name</th><th>Code</th></tr></thead>
                    <tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string) $row['id']); ?></td><td><?= e($row['name']); ?></td><td><?= e($row['code']); ?></td></tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

