<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['student']);
$classes = get_classes();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card hero-card p-4 mb-4">
    <h1 class="h3 mb-2">Student Dashboard</h1>
    <p class="mb-0">View the published class timetable in a weekly grid.</p>
</div>
<div class="card p-4">
    <a href="/automatic-college-timetable-generator/student/class-timetable.php?class_id=<?= e((string) ($classes[0]['id'] ?? 0)); ?>" class="btn btn-primary">Open Class Timetable</a>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
