<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['hod']);
$rows = get_conflicts();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card p-4">
    <h1 class="h4 mb-3">Conflict Report</h1>
    <table class="table table-striped">
        <thead><tr><th>Type</th><th>Description</th><th>Suggestion</th></tr></thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['conflict_type']); ?></td>
                    <td><?= e($row['description']); ?></td>
                    <td><?= e($row['suggestion']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>

