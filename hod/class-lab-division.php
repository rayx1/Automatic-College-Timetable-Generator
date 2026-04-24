<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    execute_query('INSERT INTO class_lab_divisions (class_id, division_name, type) VALUES (?, ?, ?)', [(int) post('class_id'), trim((string) post('division_name')), post('type')]);
    flash('success', 'Division saved.');
    redirect('/automatic-college-timetable-generator/hod/class-lab-division.php');
}

$classes = get_classes();
$rows = fetch_all('
    SELECT d.*, c.class_name, c.section, st.name AS stream_name, sem.semester_number
    FROM class_lab_divisions d
    JOIN classes c ON c.id = d.class_id
    JOIN streams st ON st.id = c.stream_id
    JOIN semesters sem ON sem.id = c.semester_id
    ORDER BY c.id, d.type
');
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Division</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Class</label><select name="class_id" class="form-select"><?php foreach ($classes as $item): ?><option value="<?= e((string) $item['id']); ?>"><?= e(get_class_display_name($item)); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Division Name</label><input name="division_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select"><option value="class">Class</option><option value="lab">Lab</option></select></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Divisions</h2>
            <table class="table table-striped">
                <thead><tr><th>Class</th><th>Division</th><th>Type</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e($row['stream_name'] . ' Sem ' . $row['semester_number'] . ' ' . $row['class_name'] . ' ' . $row['section']); ?></td><td><?= e($row['division_name']); ?></td><td><?= e($row['type']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

