<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);
require_once __DIR__ . '/../includes/timetable-generator.php';

$classes = get_classes();
$selectedClassId = isset($_GET['class_id']) ? (int) $_GET['class_id'] : (int) ($classes[0]['id'] ?? 0);
$warning = $selectedClassId ? tg_existing_warning($selectedClassId) : ['has_warning' => false, 'message' => ''];
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedClassId = (int) post('class_id');
    $action = (string) post('generation_action', 'fresh');

    if ($action === 'cancel') {
        flash('warning', 'Generation cancelled.');
        redirect('/automatic-college-timetable-generator/hod/generate-timetable.php?class_id=' . $selectedClassId);
    }

    $mode = $action === 'keep_locked' ? 'keep_locked' : 'fresh';
    $result = tg_generate_for_class($selectedClassId, $mode);
    flash($result['success'] ? 'success' : 'danger', $result['message']);
    redirect('/automatic-college-timetable-generator/hod/generate-timetable.php?class_id=' . $selectedClassId);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card p-4">
            <h1 class="h4 mb-3">Generate Timetable</h1>
            <form method="get" class="mb-4">
                <label class="form-label">Select Class</label>
                <div class="input-group">
                    <select name="class_id" class="form-select">
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= e((string) $class['id']); ?>" <?= selected($class['id'], $selectedClassId); ?>><?= e(get_class_display_name($class)); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-secondary">Load</button>
                </div>
            </form>

            <?php if ($warning['has_warning']): ?>
                <div class="alert alert-warning">
                    <strong><?= e($warning['message']); ?></strong>
                    <div class="small mt-2">Choose whether to view, regenerate, preserve locked sessions, or cancel.</div>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="class_id" value="<?= e((string) $selectedClassId); ?>">
                <div class="mb-3">
                    <label class="form-label">Action</label>
                    <select name="generation_action" class="form-select">
                        <option value="fresh">Regenerate</option>
                        <option value="keep_locked">Regenerate but keep locked sessions</option>
                        <option value="cancel">Cancel</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Generate</button>
                    <a href="/automatic-college-timetable-generator/hod/view-timetable.php?class_id=<?= e((string) $selectedClassId); ?>" class="btn btn-outline-secondary">View Existing Timetable</a>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card p-4">
            <h2 class="h5 mb-3">Generation Notes</h2>
            <ul class="mb-0">
                <li>Time slots are built from start time, end time, break, session duration, and weekly off day.</li>
                <li>Conflict checks are global across faculty, resources, and classes.</li>
                <li>Theory sessions use classroom resources and lab sessions use lab resources.</li>
                <li>Non-shared resources can only be used by the owning department.</li>
            </ul>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

