<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function tg_time_overlap(string $startA, string $endA, string $startB, string $endB): bool
{
    return strtotime($startA) < strtotime($endB) && strtotime($startB) < strtotime($endA);
}

function tg_store_conflict(string $type, string $description, string $suggestion): void
{
    execute_query(
        'INSERT INTO timetable_conflicts (conflict_type, description, suggestion) VALUES (?, ?, ?)',
        [$type, $description, $suggestion]
    );
}

function tg_clear_conflicts_for_class(int $classId): void
{
    execute_query('DELETE FROM timetable_conflicts WHERE description LIKE ?', ['%Class ID ' . $classId . '%']);
}

function tg_generate_slots(array $timing, string $weeklyOffDay): array
{
    $slots = [];
    $duration = (int) $timing['session_duration_minutes'];

    foreach (DAYS_OF_WEEK as $day) {
        if ($day === $weeklyOffDay) {
            continue;
        }

        $current = strtotime($timing['start_time']);
        $end = strtotime($timing['end_time']);
        $breakStart = $timing['break_start_time'] ? strtotime($timing['break_start_time']) : null;
        $breakEnd = $timing['break_end_time'] ? strtotime($timing['break_end_time']) : null;

        while (($current + ($duration * 60)) <= $end) {
            $slotStart = date('H:i:s', $current);
            $slotEnd = date('H:i:s', $current + ($duration * 60));

            $skipForBreak = false;
            if ($breakStart && $breakEnd) {
                $skipForBreak = tg_time_overlap($slotStart, $slotEnd, date('H:i:s', $breakStart), date('H:i:s', $breakEnd));
            }

            if (!$skipForBreak) {
                $slots[] = [
                    'day_name' => $day,
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                ];
            }

            $current += ($duration * 60);
        }
    }

    return $slots;
}

function tg_faculty_locked(int $facultyId, string $dayName, string $startTime, string $endTime): bool
{
    $locks = fetch_all(
        'SELECT * FROM faculty_locked_timings WHERE faculty_id = ? AND day_name = ?',
        [$facultyId, $dayName]
    );

    foreach ($locks as $lock) {
        if (tg_time_overlap($startTime, $endTime, $lock['start_time'], $lock['end_time'])) {
            return true;
        }
    }

    return false;
}

function tg_has_global_conflict(int $classId, int $facultyId, int $resourceId, string $dayName, string $startTime, string $endTime): ?string
{
    $entries = fetch_all('SELECT * FROM timetable WHERE day_name = ?', [$dayName]);
    foreach ($entries as $entry) {
        if (!tg_time_overlap($startTime, $endTime, $entry['start_time'], $entry['end_time'])) {
            continue;
        }

        if ((int) $entry['class_id'] === $classId) {
            return 'Class conflict';
        }
        if ((int) $entry['faculty_id'] === $facultyId) {
            return 'Faculty conflict';
        }
        if ((int) $entry['resource_id'] === $resourceId) {
            return 'Resource conflict';
        }
    }

    return null;
}

function tg_find_resource(int $classId, string $sessionType, string $dayName, string $startTime, string $endTime): ?array
{
    $departmentId = get_class_department_id($classId);
    $resourceType = $sessionType === 'lab' ? 'lab' : 'classroom';

    $resources = fetch_all(
        'SELECT * FROM resources WHERE resource_type = ? AND (is_shared = ? OR owning_department_id = ?) ORDER BY is_shared DESC, resource_name',
        [$resourceType, 'yes', $departmentId]
    );

    foreach ($resources as $resource) {
        $conflict = tg_has_global_conflict($classId, -1, (int) $resource['id'], $dayName, $startTime, $endTime);
        if ($conflict === 'Resource conflict') {
            continue;
        }

        return $resource;
    }

    return null;
}

function tg_existing_warning(int $classId): array
{
    $warningRows = fetch_all('
        SELECT DISTINCT t.class_id, t.faculty_id, t.resource_id
        FROM timetable t
        WHERE t.class_id = ?
           OR t.faculty_id IN (SELECT faculty_id FROM faculty_subjects WHERE class_id = ?)
           OR t.resource_id IN (
               SELECT t2.resource_id
               FROM timetable t2
               WHERE t2.class_id = ?
           )
    ', [$classId, $classId, $classId]);

    if ($warningRows) {
        return [
            'has_warning' => true,
            'message' => 'A timetable already exists.',
            'rows' => $warningRows,
        ];
    }

    return [
        'has_warning' => false,
        'message' => '',
        'rows' => [],
    ];
}

function tg_detect_and_store_conflicts(): void
{
    $entries = fetch_all('SELECT * FROM timetable ORDER BY day_name, start_time');
    $count = count($entries);

    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            $a = $entries[$i];
            $b = $entries[$j];

            if ($a['day_name'] !== $b['day_name']) {
                continue;
            }

            if (!tg_time_overlap($a['start_time'], $a['end_time'], $b['start_time'], $b['end_time'])) {
                continue;
            }

            if ((int) $a['faculty_id'] === (int) $b['faculty_id']) {
                tg_store_conflict('Faculty conflict', 'Faculty ID ' . $a['faculty_id'] . ' is double-booked on ' . $a['day_name'] . '.', 'Change faculty or move one session.');
            }
            if ((int) $a['resource_id'] === (int) $b['resource_id']) {
                tg_store_conflict('Resource conflict', 'Resource ID ' . $a['resource_id'] . ' is double-booked on ' . $a['day_name'] . '.', 'Change resource or add more rooms.');
            }
            if ((int) $a['class_id'] === (int) $b['class_id']) {
                tg_store_conflict('Class conflict', 'Class ID ' . $a['class_id'] . ' has overlapping sessions on ' . $a['day_name'] . '.', 'Adjust subject sessions or timings.');
            }
        }

        if (tg_faculty_locked((int) $entries[$i]['faculty_id'], $entries[$i]['day_name'], $entries[$i]['start_time'], $entries[$i]['end_time'])) {
            tg_store_conflict('Locked time violation', 'Faculty ID ' . $entries[$i]['faculty_id'] . ' is scheduled during a locked slot.', 'Remove the locked slot or regenerate.');
        }
    }
}

function tg_generate_for_class(int $classId, string $mode = 'fresh'): array
{
    execute_query('DELETE FROM timetable_conflicts');

    $class = fetch_one('
        SELECT c.*, st.name AS stream_name, sem.semester_number
        FROM classes c
        JOIN streams st ON st.id = c.stream_id
        JOIN semesters sem ON sem.id = c.semester_id
        WHERE c.id = ?
    ', [$classId]);

    if (!$class) {
        return ['success' => false, 'message' => 'Class not found.'];
    }

    $timing = fetch_one('SELECT * FROM course_timings WHERE class_id = ?', [$classId]);
    $weeklyOff = fetch_one('SELECT * FROM weekly_off_days WHERE class_id = ?', [$classId]);

    if (!$timing || !$weeklyOff) {
        tg_store_conflict('Missing timing', 'Class ID ' . $classId . ' is missing course timing or weekly off setup.', 'Configure course timing and weekly off day.');
        return ['success' => false, 'message' => 'Course timing or weekly off day is missing.'];
    }

    $slots = tg_generate_slots($timing, $weeklyOff['day_name']);
    if (!$slots) {
        return ['success' => false, 'message' => 'No valid slots available for the selected class.'];
    }

    $lockedEntries = [];
    if ($mode === 'keep_locked') {
        $lockedEntries = fetch_all('SELECT * FROM timetable WHERE class_id = ? AND is_locked = ?', [$classId, 'yes']);
        execute_query('DELETE FROM timetable WHERE class_id = ? AND is_locked = ?', [$classId, 'no']);
    } else {
        execute_query('DELETE FROM timetable WHERE class_id = ?', [$classId]);
    }

    $assignments = fetch_all('
        SELECT fs.class_id, fs.faculty_id, fs.subject_id, f.name AS faculty_name,
               sub.subject_name, sub.subject_type, sub.weekly_sessions
        FROM faculty_subjects fs
        JOIN faculty f ON f.id = fs.faculty_id
        JOIN subjects sub ON sub.id = fs.subject_id
        WHERE fs.class_id = ?
        ORDER BY sub.subject_type DESC, sub.subject_name
    ', [$classId]);

    if (!$assignments) {
        tg_store_conflict('Missing faculty', 'Class ID ' . $classId . ' has no faculty-subject assignment.', 'Map faculty to subjects before generating.');
        return ['success' => false, 'message' => 'No faculty-subject assignments found for this class.'];
    }

    $divisionMap = fetch_all('SELECT * FROM class_lab_divisions WHERE class_id = ? ORDER BY id', [$classId]);
    $placedCount = 0;
    $issues = [];

    foreach ($assignments as $assignment) {
        $required = (int) $assignment['weekly_sessions'];
        $divisionId = null;
        foreach ($divisionMap as $division) {
            if ($division['type'] === $assignment['subject_type']) {
                $divisionId = (int) $division['id'];
                break;
            }
        }

        for ($session = 0; $session < $required; $session++) {
            $placed = false;

            foreach ($slots as $slot) {
                $classConflict = fetch_one(
                    'SELECT id FROM timetable WHERE class_id = ? AND day_name = ? AND start_time = ? AND end_time = ?',
                    [$classId, $slot['day_name'], $slot['start_time'], $slot['end_time']]
                );
                if ($classConflict) {
                    continue;
                }

                if (tg_faculty_locked((int) $assignment['faculty_id'], $slot['day_name'], $slot['start_time'], $slot['end_time'])) {
                    continue;
                }

                $resource = tg_find_resource($classId, $assignment['subject_type'], $slot['day_name'], $slot['start_time'], $slot['end_time']);
                if (!$resource) {
                    continue;
                }

                $conflict = tg_has_global_conflict(
                    $classId,
                    (int) $assignment['faculty_id'],
                    (int) $resource['id'],
                    $slot['day_name'],
                    $slot['start_time'],
                    $slot['end_time']
                );

                if ($conflict !== null) {
                    continue;
                }

                execute_query(
                    'INSERT INTO timetable (class_id, subject_id, faculty_id, resource_id, division_id, day_name, start_time, end_time, session_type, is_locked)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $classId,
                        $assignment['subject_id'],
                        $assignment['faculty_id'],
                        $resource['id'],
                        $divisionId,
                        $slot['day_name'],
                        $slot['start_time'],
                        $slot['end_time'],
                        $assignment['subject_type'],
                        'no',
                    ]
                );

                $placed = true;
                $placedCount++;
                break;
            }

            if (!$placed) {
                $issue = $assignment['subject_name'] . ' could not be fully scheduled for Class ID ' . $classId . '.';
                $issues[] = $issue;
                tg_store_conflict(
                    $assignment['subject_type'] === 'lab' ? 'Missing resource' : 'Missing faculty',
                    $issue,
                    'Change faculty, change resource, add more rooms, modify timing, or adjust weekly sessions.'
                );
            }
        }
    }

    tg_detect_and_store_conflicts();

    return [
        'success' => true,
        'message' => $placedCount . ' sessions scheduled for ' . get_class_display_name($class) . '.',
        'issues' => $issues,
        'locked_kept' => count($lockedEntries),
    ];
}
