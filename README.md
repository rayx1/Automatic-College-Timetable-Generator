# Automatic College Timetable Generator

Automatic College Timetable Generator is a Core PHP (PHP 8.1) and MySQL web application that creates weekly college timetables while enforcing global conflict checks across all departments. It is designed for XAMPP and uses HTML, CSS, JavaScript, and Bootstrap for the UI.

## Problem Solved

Manual timetable creation is slow and error-prone when classrooms, labs, seminar halls, and faculty are shared across departments. This project centralizes academic data entry and generates a full weekly timetable with one click while preventing college-wide clashes.

## Features

- Role-based access for Admin, HOD, Faculty, and Student
- Management screens for departments, streams, semesters, classes, faculty, subjects, resources, timings, weekly off days, locked timings, and faculty mappings
- Global timetable generation across the entire college
- Shared resource logic for classrooms, labs, seminar halls, and workshops
- Existing timetable warning with options to view, regenerate, regenerate while keeping locked sessions, or cancel
- Conflict detection with suggestions
- Class-wise, faculty-wise, and resource-wise timetable views
- Simple faculty load report using total class count only
- Demo seed data and login credentials

## Shared Resource Rule

This system treats the college as one scheduling space. Even if data is entered department-wise, the generator checks conflicts globally:

- faculty cannot be double-booked anywhere in the college
- resources cannot be double-booked anywhere in the college
- classes cannot receive two sessions in the same slot
- non-shared resources can only be used by their owning department
- shared resources can be used by any department

## Timetable Algorithm

1. Build valid time slots from class timing settings.
2. Skip weekly off day and break interval.
3. Load faculty-subject-class mappings and subject weekly session targets.
4. Reuse locked timetable entries if requested.
5. For each required subject session, scan available slots and assign the first valid slot that passes all constraints.
6. Select resources by session type:
   - theory uses classroom resources
   - lab uses lab resources
7. Reject placements that create:
   - faculty clash
   - resource clash
   - class clash
   - locked timing violation
8. Save the generated timetable and log unresolved issues in `timetable_conflicts`.

## Conflict Handling

The app records and displays:

- Faculty conflict
- Resource conflict
- Class conflict
- Missing faculty assignment
- Missing resource
- Locked time violation

Each conflict is paired with a practical suggestion such as changing faculty, changing resource, adding more rooms, adjusting timing, removing a lock, or changing weekly session demand.

## Faculty Load

Faculty load is intentionally simple. The system only counts scheduled classes:

```sql
SELECT faculty_id, COUNT(*) AS total_classes FROM timetable GROUP BY faculty_id;
```

It does not calculate hours, overload, or assignment limits.

## Installation (XAMPP)

1. Copy the project folder to `htdocs`.
2. Create a MySQL database named `automatic_college_timetable_generator`.
3. Import `database.sql` in phpMyAdmin.
4. Update database credentials in `config/database.php` if needed.
5. Open `http://localhost/automatic-college-timetable-generator/`.

## Demo Credentials

- Admin: `admin@example.com` / `Admin@123`
- HOD: `hod@example.com` / `Hod@123`
- Faculty: `faculty@example.com` / `Faculty@123`
- Student: `student@example.com` / `Student@123`

## Future Improvements

- Smarter optimization and backtracking strategy
- Drag-and-drop manual adjustments
- PDF export and print layouts
- Faculty preference scoring
- Multi-campus support
- Attendance integration

