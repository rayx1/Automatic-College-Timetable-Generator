<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('INSERT INTO semesters (stream_id, semester_number) VALUES (?, ?)', [(int) post('stream_id'), (int) post('semester_number')]);
    flash('success', 'Semester saved.');
    redirect('/automatic-college-timetable-generator/admin/semesters.php');
}

$streams = get_streams();
$rows = get_semesters();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Semester</h1>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Stream</label>
                    <select name="stream_id" class="form-select" required>
                        <?php foreach ($streams as $stream): ?><option value="<?= e((string) $stream['id']); ?>"><?= e($stream['name']); ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Semester Number</label><input type="number" name="semester_number" class="form-control" min="1" required></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Semesters</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Stream</th><th>Semester</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string) $row['id']); ?></td><td><?= e($row['stream_name']); ?></td><td><?= e((string) $row['semester_number']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

