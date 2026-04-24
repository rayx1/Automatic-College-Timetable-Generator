<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('DELETE FROM weekly_off_days WHERE class_id = ?', [(int) post('class_id')]);
    execute_query('INSERT INTO weekly_off_days (class_id, day_name) VALUES (?, ?)', [(int) post('class_id'), post('day_name')]);
    flash('success', 'Weekly off day saved.');
    redirect('/automatic-college-timetable-generator/admin/weekly-off.php');
}

$classes = get_classes();
$rows = fetch_all('SELECT w.*, c.class_name, c.section, st.name AS stream_name, sem.semester_number FROM weekly_off_days w JOIN classes c ON c.id = w.class_id JOIN streams st ON st.id = c.stream_id JOIN semesters sem ON sem.id = c.semester_id ORDER BY c.id');
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Set Weekly Off</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Class</label><select name="class_id" class="form-select"><?php foreach ($classes as $class): ?><option value="<?= e((string) $class['id']); ?>"><?= e(get_class_display_name($class)); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Day</label><select name="day_name" class="form-select"><?php foreach ($days as $day): ?><option value="<?= e($day); ?>"><?= e($day); ?></option><?php endforeach; ?></select></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Weekly Off Days</h2>
            <table class="table table-striped">
                <thead><tr><th>Class</th><th>Weekly Off Day</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['stream_name'] . ' Sem ' . $row['semester_number'] . ' ' . $row['class_name'] . ' ' . $row['section']); ?></td><td><?= e($row['day_name']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

