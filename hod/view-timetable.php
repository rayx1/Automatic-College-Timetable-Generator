<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);

$type = $_GET['type'] ?? 'class';
$filters = [
    'class_id' => $type === 'class' ? (int) ($_GET['class_id'] ?? 0) : 0,
    'faculty_id' => $type === 'faculty' ? (int) ($_GET['faculty_id'] ?? 0) : 0,
    'resource_id' => $type === 'resource' ? (int) ($_GET['resource_id'] ?? 0) : 0,
];
$entries = get_timetable_entries($filters);
$grid = build_timetable_grid($entries);
$lists = timetable_filters();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4 mb-4">
    <h1 class="h4 mb-3">View Timetable</h1>
    <form method="get" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">View Type</label>
            <select name="type" class="form-select">
                <option value="class" <?= selected('class', $type); ?>>Class-wise</option>
                <option value="faculty" <?= selected('faculty', $type); ?>>Faculty-wise</option>
                <option value="resource" <?= selected('resource', $type); ?>>Resource-wise</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select">
                <option value="0">Select</option>
                <?php foreach ($lists['classes'] as $item): ?><option value="<?= e((string) $item['id']); ?>" <?= selected($item['id'], $filters['class_id']); ?>><?= e(get_class_display_name($item)); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Faculty</label>
            <select name="faculty_id" class="form-select">
                <option value="0">Select</option>
                <?php foreach ($lists['faculty'] as $item): ?><option value="<?= e((string) $item['id']); ?>" <?= selected($item['id'], $filters['faculty_id']); ?>><?= e($item['name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Resource</label>
            <select name="resource_id" class="form-select">
                <option value="0">Select</option>
                <?php foreach ($lists['resources'] as $item): ?><option value="<?= e((string) $item['id']); ?>" <?= selected($item['id'], $filters['resource_id']); ?>><?= e($item['resource_name']); ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Load Timetable</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <h2 class="h5 mb-3">Weekly Grid</h2>
    <?php if (!$entries): ?>
        <p class="mb-0">No timetable entries found for the selected filter.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Day</th>
                        <?php foreach (array_keys(reset($grid)) as $slot): ?><th><?= e($slot); ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grid as $day => $row): ?>
                        <tr>
                            <th><?= e($day); ?></th>
                            <?php foreach ($row as $slot => $entry): ?>
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

