<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    $user = current_user();
    redirect('/automatic-college-timetable-generator/' . $user['role'] . '/dashboard.php');
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card hero-card p-4 mb-4">
            <h1 class="h3 mb-3">Automatic College Timetable Generation System</h1>
            <p class="mb-2">A Core PHP and MySQL application that generates weekly timetables with global conflict checks across shared classrooms, labs, seminar halls, workshops, and faculty.</p>
            <div>
                <a href="/automatic-college-timetable-generator/auth/login.php" class="btn btn-light">Login</a>
            </div>
        </div>
        <div class="card p-4">
            <h2 class="h5">Key Scheduling Rules</h2>
            <ul class="mb-0">
                <li>Faculty conflicts are checked across the full college.</li>
                <li>Shared and non-shared resources are enforced globally.</li>
                <li>Break times and weekly off days are skipped automatically.</li>
                <li>Locked faculty timings and locked timetable sessions are respected.</li>
            </ul>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

