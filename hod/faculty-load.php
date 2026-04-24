<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);
$rows = get_faculty_load_report();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4">
    <h1 class="h4 mb-3">Faculty Load</h1>
    <table class="table table-striped">
        <thead><tr><th>Faculty Name</th><th>Department</th><th>Total Classes</th></tr></thead>
        <tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['faculty_name']); ?></td><td><?= e($row['department_name']); ?></td><td><?= e((string) $row['total_classes']); ?></td></tr><?php endforeach; ?></tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

