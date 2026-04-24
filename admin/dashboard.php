<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);
$stats = get_simple_stats();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Admin Dashboard</h1>
        <p class="small-muted mb-0">Manage the full college dataset and review scheduling readiness.</p>
    </div>
</div>
<div class="row g-3 mb-4">
    <?php foreach ($stats as $label => $value): ?>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="small-muted text-uppercase"><?= e(str_replace('_', ' ', $label)); ?></div>
                <div class="h3 mb-0"><?= e((string) $value); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="card p-4">
    <div class="row g-2">
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/departments.php">Departments</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/streams.php">Streams</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/semesters.php">Semesters</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/classes.php">Classes</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/faculty.php">Faculty</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/subjects.php">Subjects</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/resources.php">Resources</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/course-timings.php">Course Timings</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/weekly-off.php">Weekly Off</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/locked-timings.php">Locked Timings</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/admin/faculty-load.php">Faculty Load</a></div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

