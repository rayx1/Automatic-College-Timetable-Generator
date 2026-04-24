<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('INSERT INTO faculty_subjects (faculty_id, subject_id, class_id) VALUES (?, ?, ?)', [(int) post('faculty_id'), (int) post('subject_id'), (int) post('class_id')]);
    flash('success', 'Faculty-subject assignment saved.');
    redirect('/automatic-college-timetable-generator/hod/assign-subjects.php');
}

$faculty = get_faculty();
$subjects = get_subjects();
$classes = get_classes();
$rows = fetch_all('
    SELECT fs.id, f.name AS faculty_name, sub.subject_name, c.class_name, c.section, st.name AS stream_name, sem.semester_number
    FROM faculty_subjects fs
    JOIN faculty f ON f.id = fs.faculty_id
    JOIN subjects sub ON sub.id = fs.subject_id
    JOIN classes c ON c.id = fs.class_id
    JOIN streams st ON st.id = c.stream_id
    JOIN semesters sem ON sem.id = c.semester_id
    ORDER BY f.name
');
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Assign Subject</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Faculty</label><select name="faculty_id" class="form-select"><?php foreach ($faculty as $item): ?><option value="<?= e((string) $item['id']); ?>"><?= e($item['name']); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select"><?php foreach ($subjects as $item): ?><option value="<?= e((string) $item['id']); ?>"><?= e($item['subject_name']); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Class</label><select name="class_id" class="form-select"><?php foreach ($classes as $item): ?><option value="<?= e((string) $item['id']); ?>"><?= e(get_class_display_name($item)); ?></option><?php endforeach; ?></select></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Assignments</h2>
            <table class="table table-striped">
                <thead><tr><th>Faculty</th><th>Subject</th><th>Class</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['faculty_name']); ?></td><td><?= e($row['subject_name']); ?></td><td><?= e($row['stream_name'] . ' Sem ' . $row['semester_number'] . ' ' . $row['class_name'] . ' ' . $row['section']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

