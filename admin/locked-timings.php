<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('INSERT INTO faculty_locked_timings (faculty_id, day_name, start_time, end_time) VALUES (?, ?, ?, ?)', [(int) post('faculty_id'), post('day_name'), post('start_time'), post('end_time')]);
    flash('success', 'Locked timing saved.');
    redirect('/automatic-college-timetable-generator/admin/locked-timings.php');
}

$faculty = get_faculty();
$rows = fetch_all('SELECT flt.*, f.name AS faculty_name FROM faculty_locked_timings flt JOIN faculty f ON f.id = flt.faculty_id ORDER BY f.name, flt.day_name');
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Locked Timing</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Faculty</label><select name="faculty_id" class="form-select"><?php foreach ($faculty as $item): ?><option value="<?= e((string) $item['id']); ?>"><?= e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Day</label><select name="day_name" class="form-select"><?php foreach ($days as $day): ?><option value="<?= e($day); ?>"><?= e($day); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Start Time</label><input type="time" name="start_time" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control" required></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Locked Timings</h2>
            <table class="table table-striped">
                <thead><tr><th>Faculty</th><th>Day</th><th>From</th><th>To</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['faculty_name']); ?></td><td><?= e($row['day_name']); ?></td><td><?= e(substr($row['start_time'], 0, 5)); ?></td><td><?= e(substr($row['end_time'], 0, 5)); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

