DROP DATABASE IF EXISTS automatic_college_timetable_generator;
CREATE DATABASE automatic_college_timetable_generator CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE automatic_college_timetable_generator;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hod', 'faculty', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE
);

CREATE TABLE streams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    CONSTRAINT fk_stream_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    semester_number INT NOT NULL,
    CONSTRAINT fk_semester_stream FOREIGN KEY (stream_id) REFERENCES streams(id) ON DELETE CASCADE
);

CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    semester_id INT NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    section VARCHAR(20) NOT NULL,
    CONSTRAINT fk_class_stream FOREIGN KEY (stream_id) REFERENCES streams(id) ON DELETE CASCADE,
    CONSTRAINT fk_class_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    CONSTRAINT fk_faculty_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_faculty_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    semester_id INT NOT NULL,
    subject_name VARCHAR(120) NOT NULL,
    subject_type ENUM('theory', 'lab') NOT NULL,
    weekly_sessions INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_subject_stream FOREIGN KEY (stream_id) REFERENCES streams(id) ON DELETE CASCADE,
    CONSTRAINT fk_subject_semester FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_name VARCHAR(120) NOT NULL,
    resource_type ENUM('classroom', 'lab', 'seminar', 'workshop') NOT NULL,
    capacity INT NOT NULL DEFAULT 0,
    owning_department_id INT NULL,
    is_shared ENUM('yes', 'no') NOT NULL DEFAULT 'yes',
    CONSTRAINT fk_resource_department FOREIGN KEY (owning_department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE course_timings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    session_duration_minutes INT NOT NULL,
    break_start_time TIME NULL,
    break_end_time TIME NULL,
    CONSTRAINT fk_timing_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE weekly_off_days (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    day_name VARCHAR(20) NOT NULL,
    CONSTRAINT fk_weekly_off_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE faculty_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    CONSTRAINT fk_faculty_subject_faculty FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
    CONSTRAINT fk_faculty_subject_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_faculty_subject_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE class_lab_divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    division_name VARCHAR(80) NOT NULL,
    type ENUM('class', 'lab') NOT NULL,
    CONSTRAINT fk_division_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE faculty_locked_timings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    day_name VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    CONSTRAINT fk_locked_faculty FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE
);

CREATE TABLE timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    faculty_id INT NOT NULL,
    resource_id INT NOT NULL,
    division_id INT NULL,
    day_name VARCHAR(20) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    session_type ENUM('theory', 'lab') NOT NULL,
    is_locked ENUM('yes', 'no') NOT NULL DEFAULT 'no',
    CONSTRAINT fk_timetable_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_timetable_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_timetable_faculty FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
    CONSTRAINT fk_timetable_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    CONSTRAINT fk_timetable_division FOREIGN KEY (division_id) REFERENCES class_lab_divisions(id) ON DELETE SET NULL
);

CREATE TABLE timetable_conflicts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conflict_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    suggestion TEXT NOT NULL
);

INSERT INTO users (id, name, email, password, role) VALUES
(1, 'System Admin', 'admin@example.com', '$2y$10$hGSPnIzbLUBidzIcn09oSePc9LQ9DUI76oBthcmBPuNzdrcZjlCbe', 'admin'),
(2, 'CSE HOD', 'hod@example.com', '$2y$10$83QJ4lQ0SFv2jwKdLDMWwe9vcyZbuSP98aAHJe26jYK7hHLRDfyIS', 'hod'),
(3, 'Alice Faculty', 'faculty@example.com', '$2y$10$FNbqhiNuX1rXkiS5aLqxnOfm5HjHkqNhUj42Qo6I0WyvjRy7nkHSG', 'faculty'),
(4, 'Student Viewer', 'student@example.com', '$2y$10$RvWXiaPdqYa4YMkXbknnROtbVWbjPbd8CbudXf5RvWiJ6acU/vQa6', 'student'),
(5, 'Bob Faculty', 'bob.faculty@example.com', '$2y$10$FNbqhiNuX1rXkiS5aLqxnOfm5HjHkqNhUj42Qo6I0WyvjRy7nkHSG', 'faculty'),
(6, 'Carol Faculty', 'carol.faculty@example.com', '$2y$10$FNbqhiNuX1rXkiS5aLqxnOfm5HjHkqNhUj42Qo6I0WyvjRy7nkHSG', 'faculty'),
(7, 'David Faculty', 'david.faculty@example.com', '$2y$10$FNbqhiNuX1rXkiS5aLqxnOfm5HjHkqNhUj42Qo6I0WyvjRy7nkHSG', 'faculty'),
(8, 'Eva Faculty', 'eva.faculty@example.com', '$2y$10$FNbqhiNuX1rXkiS5aLqxnOfm5HjHkqNhUj42Qo6I0WyvjRy7nkHSG', 'faculty');

INSERT INTO departments (id, name, code) VALUES
(1, 'Computer Science', 'CSE'),
(2, 'Electronics', 'ECE');

INSERT INTO streams (id, department_id, name, code) VALUES
(1, 1, 'BCA', 'BCA'),
(2, 2, 'BSc Electronics', 'BSE');

INSERT INTO semesters (id, stream_id, semester_number) VALUES
(1, 1, 1),
(2, 1, 3),
(3, 2, 1);

INSERT INTO classes (id, stream_id, semester_id, class_name, section) VALUES
(1, 1, 1, 'BCA-A', 'A'),
(2, 1, 2, 'BCA-B', 'B'),
(3, 2, 3, 'BSE-A', 'A');

INSERT INTO faculty (id, user_id, name, department_id) VALUES
(1, 3, 'Alice Faculty', 1),
(2, 5, 'Bob Faculty', 1),
(3, 6, 'Carol Faculty', 1),
(4, 7, 'David Faculty', 2),
(5, 8, 'Eva Faculty', 2);

INSERT INTO subjects (id, stream_id, semester_id, subject_name, subject_type, weekly_sessions) VALUES
(1, 1, 1, 'Programming Fundamentals', 'theory', 4),
(2, 1, 1, 'Programming Lab', 'lab', 2),
(3, 1, 1, 'Mathematics I', 'theory', 3),
(4, 1, 2, 'Data Structures', 'theory', 4),
(5, 1, 2, 'Data Structures Lab', 'lab', 2),
(6, 1, 2, 'Database Systems', 'theory', 3),
(7, 2, 3, 'Digital Electronics', 'theory', 4),
(8, 2, 3, 'Circuit Lab', 'lab', 2);

INSERT INTO resources (id, resource_name, resource_type, capacity, owning_department_id, is_shared) VALUES
(1, 'CR-101', 'classroom', 60, 1, 'yes'),
(2, 'CR-102', 'classroom', 60, 1, 'yes'),
(3, 'CR-201', 'classroom', 50, 2, 'yes'),
(4, 'CR-202', 'classroom', 50, 2, 'yes'),
(5, 'Seminar Hall', 'classroom', 120, NULL, 'yes'),
(6, 'Lab-1', 'lab', 35, 1, 'yes'),
(7, 'Lab-2', 'lab', 35, 1, 'yes'),
(8, 'Circuit Lab', 'lab', 30, 2, 'yes');

INSERT INTO course_timings (id, class_id, start_time, end_time, session_duration_minutes, break_start_time, break_end_time) VALUES
(1, 1, '09:00:00', '15:00:00', 60, '12:00:00', '13:00:00'),
(2, 2, '09:00:00', '15:00:00', 60, '12:00:00', '13:00:00'),
(3, 3, '09:00:00', '15:00:00', 60, '12:00:00', '13:00:00');

INSERT INTO weekly_off_days (id, class_id, day_name) VALUES
(1, 1, 'Sunday'),
(2, 2, 'Saturday'),
(3, 3, 'Sunday');

INSERT INTO faculty_subjects (id, faculty_id, subject_id, class_id) VALUES
(1, 1, 1, 1),
(2, 2, 2, 1),
(3, 3, 3, 1),
(4, 1, 4, 2),
(5, 2, 5, 2),
(6, 3, 6, 2),
(7, 4, 7, 3),
(8, 5, 8, 3);

INSERT INTO class_lab_divisions (id, class_id, division_name, type) VALUES
(1, 1, 'Whole Class', 'class'),
(2, 1, 'Lab Batch A', 'lab'),
(3, 2, 'Whole Class', 'class'),
(4, 2, 'Lab Batch B', 'lab'),
(5, 3, 'Whole Class', 'class'),
(6, 3, 'Lab Batch C', 'lab');

INSERT INTO faculty_locked_timings (id, faculty_id, day_name, start_time, end_time) VALUES
(1, 1, 'Monday', '09:00:00', '10:00:00'),
(2, 2, 'Wednesday', '14:00:00', '15:00:00'),
(3, 4, 'Friday', '11:00:00', '12:00:00');
