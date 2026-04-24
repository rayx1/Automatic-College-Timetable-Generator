<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('DELETE FROM course_timings WHERE class_id = ?', [(int) post('class_id')]);
    execute_query('INSERT INTO course_timings (class_id, start_time, end_time, session_duration_minutes, break_start_time, break_end_time) VALUES (?, ?, ?, ?, ?, ?)', [(int) post('class_id'), post('start_time'), post('end_time'), (int) post('session_duration_minutes'), post('break_start_time') ?: null, post('break_end_time') ?: null]);
    flash('success', 'Course timing saved.');
    redirect('/automatic-college-timetable-generator/admin/course-timings.php');
}

$classes = get_classes();
$rows = fetch_all('SELECT ct.*, c.class_name, c.section, st.name AS stream_name, sem.semester_number FROM course_timings ct JOIN classes c ON c.id = ct.class_id JOIN streams st ON st.id = c.stream_id JOIN semesters sem ON sem.id = c.semester_id ORDER BY c.id');
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Set Course Timing</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Class</label><select name="class_id" class="form-select"><?php foreach ($classes as $class): ?><option value="<?= e((string) $class['id']); ?>"><?= e(get_class_display_name($class)); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Start Time</label><input type="time" name="start_time" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">End Time</label><input type="time" name="end_time" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Session Duration (minutes)</label><input type="number" name="session_duration_minutes" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Break Start</label><input type="time" name="break_start_time" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Break End</label><input type="time" name="break_end_time" class="form-control"></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Configured Timings</h2>
            <table class="table table-striped">
                <thead><tr><th>Class</th><th>Start</th><th>End</th><th>Session</th><th>Break</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['stream_name'] . ' Sem ' . $row['semester_number'] . ' ' . $row['class_name'] . ' ' . $row['section']); ?></td><td><?= e(substr($row['start_time'], 0, 5)); ?></td><td><?= e(substr($row['end_time'], 0, 5)); ?></td><td><?= e((string) $row['session_duration_minutes']); ?> mins</td><td><?= e(($row['break_start_time'] ? substr($row['break_start_time'], 0, 5) : '-') . ' to ' . ($row['break_end_time'] ? substr($row['break_end_time'], 0, 5) : '-')); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

