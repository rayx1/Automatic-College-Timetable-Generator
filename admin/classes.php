<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('INSERT INTO classes (stream_id, semester_id, class_name, section) VALUES (?, ?, ?, ?)', [(int) post('stream_id'), (int) post('semester_id'), trim((string) post('class_name')), trim((string) post('section'))]);
    flash('success', 'Class saved.');
    redirect('/automatic-college-timetable-generator/admin/classes.php');
}

$streams = get_streams();
$semesters = get_semesters();
$rows = get_classes();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Class</h1>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Stream</label>
                    <select name="stream_id" class="form-select" required>
                        <?php foreach ($streams as $stream): ?><option value="<?= e((string) $stream['id']); ?>"><?= e($stream['name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select" required>
                        <?php foreach ($semesters as $semester): ?><option value="<?= e((string) $semester['id']); ?>"><?= e($semester['stream_name'] . ' Sem ' . $semester['semester_number']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Class Name</label><input name="class_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Section</label><input name="section" class="form-control" required></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Classes</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Stream</th><th>Semester</th><th>Class</th><th>Section</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string) $row['id']); ?></td><td><?= e($row['stream_name']); ?></td><td><?= e((string) $row['semester_number']); ?></td><td><?= e($row['class_name']); ?></td><td><?= e($row['section']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

