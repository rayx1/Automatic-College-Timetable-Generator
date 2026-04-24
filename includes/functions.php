<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DAYS_OF_WEEK = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function app_url(string $path = ''): string
{
    return $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('danger', 'Please login first.');
        redirect('/automatic-college-timetable-generator/auth/login.php');
    }
}

function require_role(array $roles): void
{
    require_login();
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        flash('danger', 'You are not authorized to access that page.');
        redirect('/automatic-college-timetable-generator/index.php');
    }
}

function post(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function fetch_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

function execute_query(string $sql, array $params = []): bool
{
    $stmt = db()->prepare($sql);
    return $stmt->execute($params);
}

function fetch_pairs(string $sql, array $params = []): array
{
    $items = fetch_all($sql, $params);
    $pairs = [];
    foreach ($items as $item) {
        $pairs[$item['id']] = $item['label'];
    }
    return $pairs;
}

function selected($value, $current): string
{
    return (string) $value === (string) $current ? 'selected' : '';
}

function count_table(string $table): int
{
    $row = fetch_one("SELECT COUNT(*) AS total FROM {$table}");
    return (int) ($row['total'] ?? 0);
}

function get_departments(): array
{
    return fetch_all('SELECT id, name, code FROM departments ORDER BY name');
}

function get_streams(): array
{
    return fetch_all('SELECT s.*, d.name AS department_name FROM streams s JOIN departments d ON d.id = s.department_id ORDER BY s.name');
}

function get_semesters(): array
{
    return fetch_all('
        SELECT sem.id, sem.semester_number, st.name AS stream_name
        FROM semesters sem
        JOIN streams st ON st.id = sem.stream_id
        ORDER BY st.name, sem.semester_number
    ');
}

function get_classes(): array
{
    return fetch_all('
        SELECT c.*, st.name AS stream_name, sem.semester_number
        FROM classes c
        JOIN streams st ON st.id = c.stream_id
        JOIN semesters sem ON sem.id = c.semester_id
        ORDER BY st.name, sem.semester_number, c.class_name, c.section
    ');
}

function get_faculty(): array
{
    return fetch_all('
        SELECT f.*, u.email, d.name AS department_name
        FROM faculty f
        JOIN users u ON u.id = f.user_id
        JOIN departments d ON d.id = f.department_id
        ORDER BY f.name
    ');
}

function get_subjects(): array
{
    return fetch_all('
        SELECT sub.*, st.name AS stream_name, sem.semester_number
        FROM subjects sub
        JOIN streams st ON st.id = sub.stream_id
        JOIN semesters sem ON sem.id = sub.semester_id
        ORDER BY st.name, sem.semester_number, sub.subject_name
    ');
}

function get_resource_records(): array
{
    return fetch_all('
        SELECT r.*, d.name AS owning_department_name
        FROM resources r
        LEFT JOIN departments d ON d.id = r.owning_department_id
        ORDER BY r.resource_type, r.resource_name
    ');
}

function get_class_display_name(array $class): string
{
    return trim($class['stream_name'] . ' Sem ' . $class['semester_number'] . ' ' . $class['class_name'] . ' ' . $class['section']);
}

function get_faculty_load_report(): array
{
    return fetch_all('
        SELECT f.id AS faculty_id, f.name AS faculty_name, d.name AS department_name, COUNT(t.id) AS total_classes
        FROM faculty f
        JOIN departments d ON d.id = f.department_id
        LEFT JOIN timetable t ON t.faculty_id = f.id
        GROUP BY f.id, f.name, d.name
        ORDER BY f.name
    ');
}

function get_user_faculty_record(int $userId): ?array
{
    return fetch_one('SELECT * FROM faculty WHERE user_id = ?', [$userId]);
}

function get_simple_stats(): array
{
    return [
        'departments' => count_table('departments'),
        'streams' => count_table('streams'),
        'classes' => count_table('classes'),
        'faculty' => count_table('faculty'),
        'subjects' => count_table('subjects'),
        'resources' => count_table('resources'),
        'sessions' => count_table('timetable'),
    ];
}

function timetable_filters(): array
{
    return [
        'classes' => get_classes(),
        'faculty' => get_faculty(),
        'resources' => get_resource_records(),
    ];
}

function get_class_department_id(int $classId): ?int
{
    $row = fetch_one('
        SELECT d.id AS department_id
        FROM classes c
        JOIN streams s ON s.id = c.stream_id
        JOIN departments d ON d.id = s.department_id
        WHERE c.id = ?
    ', [$classId]);
    return $row ? (int) $row['department_id'] : null;
}

function get_timetable_entries(array $filters = []): array
{
    $conditions = [];
    $params = [];

    if (!empty($filters['class_id'])) {
        $conditions[] = 't.class_id = ?';
        $params[] = $filters['class_id'];
    }
    if (!empty($filters['faculty_id'])) {
        $conditions[] = 't.faculty_id = ?';
        $params[] = $filters['faculty_id'];
    }
    if (!empty($filters['resource_id'])) {
        $conditions[] = 't.resource_id = ?';
        $params[] = $filters['resource_id'];
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

    return fetch_all("
        SELECT t.*, sub.subject_name, f.name AS faculty_name, r.resource_name,
               c.class_name, c.section, st.name AS stream_name, sem.semester_number,
               divs.division_name
        FROM timetable t
        JOIN subjects sub ON sub.id = t.subject_id
        JOIN faculty f ON f.id = t.faculty_id
        JOIN resources r ON r.id = t.resource_id
        JOIN classes c ON c.id = t.class_id
        JOIN streams st ON st.id = c.stream_id
        JOIN semesters sem ON sem.id = c.semester_id
        LEFT JOIN class_lab_divisions divs ON divs.id = t.division_id
        {$where}
        ORDER BY FIELD(t.day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), t.start_time
    ", $params);
}

function build_timetable_grid(array $entries): array
{
    $slots = [];
    foreach ($entries as $entry) {
        $slotKey = substr($entry['start_time'], 0, 5) . ' - ' . substr($entry['end_time'], 0, 5);
        $slots[$slotKey] = true;
    }

    $grid = [];
    foreach (DAYS_OF_WEEK as $day) {
        $grid[$day] = [];
        foreach (array_keys($slots) as $slotLabel) {
            $grid[$day][$slotLabel] = null;
        }
    }

    foreach ($entries as $entry) {
        $slotKey = substr($entry['start_time'], 0, 5) . ' - ' . substr($entry['end_time'], 0, 5);
        $grid[$entry['day_name']][$slotKey] = $entry;
    }

    return $grid;
}

function get_conflicts(): array
{
    return fetch_all('SELECT * FROM timetable_conflicts ORDER BY id DESC');
}

function warning_for_existing_timetable(?int $classId = null): array
{
    if (!$classId) {
        return [];
    }

    $records = fetch_all('
        SELECT DISTINCT t.id, t.class_id, t.faculty_id, t.resource_id
        FROM timetable t
        WHERE t.class_id = ?
           OR t.faculty_id IN (SELECT faculty_id FROM faculty_subjects WHERE class_id = ?)
           OR t.resource_id IN (
               SELECT resource_id FROM timetable WHERE class_id = ?
           )
        LIMIT 5
    ', [$classId, $classId, $classId]);

    return $records;
}
