<?php
require_once __DIR__ . '/../includes/functions.php';
session_destroy();
redirect('/automatic-college-timetable-generator/auth/login.php');
