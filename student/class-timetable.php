<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['student', 'admin', 'hod']);
$classes = get_classes();
$classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
$entries = $classId ? get_timetable_entries(['class_id' => $classId]) : [];
$grid = build_timetable_grid($entries);
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4 mb-4">
    <h1 class="h4 mb-3">Class Timetable</h1>
    <form method="get" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <?php foreach ($classes as $class): ?><option value="<?= e((string) $class['id']); ?>" <?= selected($class['id'], $classId); ?>><?= e(get_class_display_name($class)); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <button class="btn btn-primary">Load</button>
        </div>
    </form>
</div>
<div class="card p-4">
    <?php if (!$entries): ?>
        <p class="mb-0">No timetable found for the selected class.</p>
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
                                        <span><?= e($entry['faculty_name']); ?></span><br>
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
