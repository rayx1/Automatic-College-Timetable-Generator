<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owningDepartmentId = post('owning_department_id') !== '' ? (int) post('owning_department_id') : null;
    execute_query('INSERT INTO resources (resource_name, resource_type, capacity, owning_department_id, is_shared) VALUES (?, ?, ?, ?, ?)', [trim((string) post('resource_name')), post('resource_type'), (int) post('capacity'), $owningDepartmentId, post('is_shared')]);
    flash('success', 'Resource saved.');
    redirect('/automatic-college-timetable-generator/admin/resources.php');
}

$departments = get_departments();
$rows = get_resource_records();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h1 class="h5 mb-3">Add Resource</h1>
            <form method="post">
                <div class="mb-3"><label class="form-label">Resource Name</label><input name="resource_name" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Type</label><select name="resource_type" class="form-select"><option value="classroom">Classroom</option><option value="lab">Lab</option><option value="seminar">Seminar</option><option value="workshop">Workshop</option></select></div>
                <div class="mb-3"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Owning Department</label><select name="owning_department_id" class="form-select"><option value="">None</option><?php foreach ($departments as $department): ?><option value="<?= e((string) $department['id']); ?>"><?= e($department['name']); ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label">Shared</label><select name="is_shared" class="form-select"><option value="yes">Yes</option><option value="no">No</option></select></div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card p-4">
            <h2 class="h5 mb-3">Resources</h2>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Capacity</th><th>Owner</th><th>Shared</th></tr></thead>
                <tbody><?php foreach ($rows as $row): ?><tr><td><?= e((string) $row['id']); ?></td><td><?= e($row['resource_name']); ?></td><td><?= e($row['resource_type']); ?></td><td><?= e((string) $row['capacity']); ?></td><td><?= e($row['owning_department_name'] ?? 'N/A'); ?></td><td><?= e($row['is_shared']); ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
