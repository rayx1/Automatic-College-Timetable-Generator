<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['faculty']);
$user = current_user();
$facultyRecord = get_user_faculty_record((int) $user['id']);
$entries = $facultyRecord ? get_timetable_entries(['faculty_id' => (int) $facultyRecord['id']]) : [];
$grid = build_timetable_grid($entries);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4">
    <h1 class="h4 mb-3">My Timetable</h1>
    <?php if (!$facultyRecord): ?>
        <p class="mb-0">No faculty profile linked to this account.</p>
    <?php elseif (!$entries): ?>
        <p class="mb-0">No timetable assigned yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Day</th><?php foreach (array_keys(reset($grid)) as $slot): ?><th><?= e($slot); ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($grid as $day => $row): ?>
                        <tr>
                            <th><?= e($day); ?></th>
                            <?php foreach ($row as $entry): ?>
                                <td class="timetable-cell">
                                    <?php if ($entry): ?>
                                        <strong><?= e($entry['subject_name']); ?></strong><br>
                                        <span><?= e($entry['stream_name'] . ' Sem ' . $entry['semester_number'] . ' ' . $entry['class_name'] . ' ' . $entry['section']); ?></span><br>
                                        <span><?= e($entry['resource_name']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

