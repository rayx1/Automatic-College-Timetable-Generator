<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);
$stats = get_simple_stats();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card hero-card p-4">
            <h1 class="h3 mb-2">HOD Dashboard</h1>
            <p class="mb-0">Assign faculty, define class divisions, generate timetables, and inspect conflicts.</p>
        </div>
    </div>
    <?php foreach (['classes', 'faculty', 'subjects', 'sessions'] as $key): ?>
        <div class="col-md-3">
            <div class="card stat-card p-3">
                <div class="small-muted text-uppercase"><?= e($key); ?></div>
                <div class="h3 mb-0"><?= e((string) $stats[$key]); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="card p-4">
    <div class="row g-2">
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/hod/assign-subjects.php">Assign Subjects</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/hod/class-lab-division.php">Class/Lab Division</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/hod/generate-timetable.php">Generate Timetable</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/hod/view-timetable.php">View Timetable</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/hod/conflicts.php">Conflicts</a></div>
        <div class="col-md-4"><a class="btn btn-outline-primary w-100" href="/automatic-college-timetable-generator/hod/faculty-load.php">Faculty Load</a></div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

