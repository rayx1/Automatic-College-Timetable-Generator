<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['faculty']);
$user = current_user();
$facultyRecord = get_user_faculty_record((int) $user['id']);
$totalClasses = 0;
if ($facultyRecord) {
    $row = fetch_one('SELECT COUNT(*) AS total_classes FROM timetable WHERE faculty_id = ?', [$facultyRecord['id']]);
    $totalClasses = (int) ($row['total_classes'] ?? 0);
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card hero-card p-4 mb-4">
    <h1 class="h3 mb-2">Faculty Dashboard</h1>
    <p class="mb-0">Total Classes This Week: <?= e((string) $totalClasses); ?></p>
</div>
<div class="card p-4">
    <a href="/automatic-college-timetable-generator/faculty/my-timetable.php" class="btn btn-primary">View My Timetable</a>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

