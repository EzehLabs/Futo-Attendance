<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FUTO Attendance | Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">F</div>
                <div><b>FUTO ATTENDANCE</b><small>MVP Management</small></div>
            </div>
            <nav class="nav">
                <h6>Overview</h6><button data-view="dashboard" class="active">⌂ Dashboard</button>
                <h6>Academic Setup</h6><button data-view="students">♙ Students</button><button data-view="lecturers">♟ Lecturers</button><button data-view="courses">▦ Courses</button><button data-view="enrolments">☷ Enrolments</button><button data-view="venues">⌂ Venues</button>
                <h6>Attendance</h6><button data-view="attendance">✓ Attendance</button><button data-view="simulator">▶ Test Attendance</button><button data-view="devices">▣ ZKTeco Devices</button><button data-view="unmatched">!</button>
                <h6>System</h6><button data-view="reports">▤ Reports</button><button data-view="audit">◈ Audit Log</button><button data-view="settings">⚙ Settings</button>
            </nav>
            <div class="sidebottom">
                <div class="mini">
                    <div class="avatar">AD</div>
                    <div><b id="sideName">Admin</b><small>Administrator / HOD</small></div>
                </div><button class="logout" data-logout>↪ Logout</button>
            </div>
        </aside>
        <main class="main">
            <header class="top">
                <div class="topLeft"><button class="menu">☰</button>
                    <div>
                        <div class="title" id="pageTitle">Dashboard</div>
                        <div class="sub">Functional MVP administration</div>
                    </div>
                </div>
                <div class="topUser">
                    <div class="avatar">AD</div>
                    <div><b id="topName">Admin</b><small>Administrator</small></div>
                </div>
            </header>
            <div class="content">
                <section id="dashboard" class="view active">
                    <div class="welcome">
                        <div>
                            <h1>Admin Dashboard</h1>
                            <p>Everything needed to set up classes and test attendance without the ZKTeco device.</p>
                        </div><button class="btn btnPrimary" data-go="simulator">▶ Simulate Punch</button>
                    </div>
                    <div class="stats">
                        <div class="stat blue">
                            <div class="statIcon">♙</div>
                            <div class="statValue" id="stStudents">0</div>
                            <div class="statLabel">Students</div>
                        </div>
                        <div class="stat purple">
                            <div class="statIcon">♟</div>
                            <div class="statValue" id="stLecturers">0</div>
                            <div class="statLabel">Lecturers</div>
                        </div>
                        <div class="stat orange">
                            <div class="statIcon">▦</div>
                            <div class="statValue" id="stCourses">0</div>
                            <div class="statLabel">Active Courses</div>
                        </div>
                        <div class="stat green">
                            <div class="statIcon">✓</div>
                            <div class="statValue" id="stAttendance">0</div>
                            <div class="statLabel">Attendance Today</div>
                        </div>
                    </div>
                    <div class="grid2">
                        <div class="card">
                            <div class="cardHead">
                                <h2>Quick workflow</h2>
                            </div>
                            <div class="cardBody">
                                <ol>
                                    <li>Create/confirm students and lecturers, including bulk registration.</li>
                                    <li>Create courses and assign lecturers.</li>
                                    <li>Enrol students individually or in bulk.</li>
                                    <li>Create a venue and configure its attendance device.</li>
                                    <li>Lecturers schedule and close their own class sessions.</li>
                                    <li>Use <b>Test Attendance</b> to simulate a ZKTeco punch; overdue sessions close automatically.</li>
                                </ol>
                            </div>
                        </div>
                        <div class="card">
                            <div class="cardHead">
                                <h2>System status</h2>
                            </div>
                            <div class="cardBody" id="systemStatus">Loading…</div>
                        </div>
                    </div>
                </section>

                <section id="students" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Students</h1>
                            <p>Create, activate/deactivate and inspect student accounts.</p>
                        </div><button class="btn btnPrimary" data-action="showStudentForm">+ Add Student</button>
                    </div>
                    <div class="card"><div class="cardBody"><h3>Bulk Student Registration</h3><p class="secondary">Upload a CSV exported from Excel. Required columns: login_id, first_name, last_name. Optional: email, phone, department, level, password.</p><form id="bulkStudentsForm" class="formGrid"><input type="file" name="file" accept=".csv,text/csv" required><button class="btn btnSecondary">Import Students</button></form><div id="bulkStudentsResult" class="secondary"></div></div></div><div id="studentForm" class="card hidden">
                        <div class="cardHead">
                            <h2>New Student</h2>
                        </div>
                        <div class="cardBody">
                            <form class="formGrid" id="studentCreateForm"><input name="login_id" placeholder="Matric number" required><input name="password" value="password" placeholder="Temporary password" required><input name="first_name" placeholder="First name" required><input name="last_name" placeholder="Last name" required><input name="email" placeholder="Email"><input name="phone" placeholder="Phone"><select name="department" required><option>Computer Science</option><option>IFT</option><option>Cybersecurity</option><option>Software Engineering</option></select><select name="level" required><option value="100">100 Level</option><option value="200">200 Level</option><option value="300">300 Level</option><option value="400" selected>400 Level</option></select>
                                <div><button class="btn btnPrimary">Create</button><button type="button" class="btn btnSecondary" data-action="hideStudentForm">Cancel</button></div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Student Directory</h2><span class="badge info" id="studentCount">0</span>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Matric</th>
                                        <th>Department</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="lecturers" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Lecturers</h1>
                            <p>Manage lecturer accounts. Course assignment is done from Courses.</p>
                        </div><button class="btn btnPrimary" data-action="showLecturerForm">+ Add Lecturer</button>
                    </div>
                    <div class="card"><div class="cardBody"><h3>Bulk Lecturer Registration</h3><p class="secondary">Upload a CSV exported from Excel. Required columns: login_id, first_name, last_name. Optional: email, phone, department, password.</p><form id="bulkLecturersForm" class="formGrid"><input type="file" name="file" accept=".csv,text/csv" required><button class="btn btnSecondary">Import Lecturers</button></form><div id="bulkLecturersResult" class="secondary"></div></div></div><div id="lecturerForm" class="card hidden">
                        <div class="cardHead">
                            <h2>New Lecturer</h2>
                        </div>
                        <div class="cardBody">
                            <form class="formGrid" id="lecturerCreateForm"><input name="login_id" placeholder="Staff ID" required><input name="password" value="password" required><input name="first_name" placeholder="First name" required><input name="last_name" placeholder="Last name" required><input name="email" placeholder="Email"><input name="phone" placeholder="Phone"><input name="department" value="Computer Science" placeholder="Department">
                                <div><button class="btn btnPrimary">Create</button><button type="button" class="btn btnSecondary" data-action="hideLecturerForm">Cancel</button></div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Lecturer Directory</h2><span class="badge info" id="lecturerCount">0</span>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="lecturersTable">
                                <thead>
                                    <tr>
                                        <th>Lecturer</th>
                                        <th>Staff ID</th>
                                        <th>Department</th>
                                        <th>Courses</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="courses" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Courses</h1>
                            <p>Create courses and assign lecturers.</p>
                        </div><button class="btn btnPrimary" data-action="showCourseForm">+ Add Course</button>
                    </div>
                    <div id="courseForm" class="card hidden">
                        <div class="cardHead">
                            <h2>New Course</h2>
                        </div>
                        <div class="cardBody">
                            <form class="formGrid" id="courseCreateForm"><input name="code" placeholder="CSC 410" required><input name="title" placeholder="Course title" required><select name="department"><option>Computer Science</option><option>IFT</option><option>Cybersecurity</option><option>Software Engineering</option></select><input name="semester" value="First Semester"><input name="planned_sessions" type="number" min="1" max="100" value="17" placeholder="Planned classes"><select name="lecturer_id" id="courseLecturer"></select>
                                <div><button class="btn btnPrimary">Create Course</button><button type="button" class="btn btnSecondary" data-action="hideCourseForm">Cancel</button></div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Course Directory</h2>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="coursesTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Lecturer(s)</th>
                                        <th>Students</th>
                                        <th>Semester</th>
                                        <th>Planned Classes</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="enrolments" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Enrolments</h1>
                            <p>Add or remove students from courses.</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Add Student to Course</h2>
                        </div>
                        <div class="cardBody">
                            <form class="formGrid" id="enrolForm"><select name="course_id" id="enrolCourse" required></select><select name="student_id" id="enrolStudent" required></select><button class="btn btnPrimary">Enrol Student</button></form><hr><h3>Bulk Course Enrolment</h3><p class="secondary">Select a course and multiple students, then enrol them together.</p><form class="formGrid" id="bulkEnrolForm"><select name="course_id" id="bulkEnrolCourse" required></select><select name="student_ids" id="bulkEnrolStudents" multiple size="6" required></select><button class="btn btnSecondary">Enrol Selected Students</button></form><div id="bulkEnrolResult" class="secondary"></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Current Enrolments</h2><span class="badge info" id="enrolCount">0</span>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="enrolTable">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Student</th>
                                        <th>Matric</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="venues" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Venues</h1>
                            <p>Venues are used to bind a session to the correct attendance device.</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardBody">
                            <form class="inlineForm" id="venueForm"><input name="name" placeholder="e.g. LT 5" required><button class="btn btnPrimary">Add Venue</button></form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Venues</h2>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="venuesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="attendance" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Attendance</h1>
                            <p>View all records and make controlled corrections with a reason.</p>
                        </div>
                        <div class="actions"><button class="btn btnSecondary" data-action="refreshAttendance">Refresh</button><a class="btn btnPrimary" href="api.php?route=admin/reports/attendance.csv">Download CSV</a></div>
                    </div>
                    <div class="card">
                        <div class="tableWrap">
                            <table class="table" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Course</th>
                                        <th>Student</th>
                                        <th>Status</th>
                                        <th>Source</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="simulator" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Test Attendance / ZKTeco Simulator</h1>
                            <p>This replaces the real device for testing. It uses the same punch-processing logic as the ZKTeco endpoint.</p>
                        </div>
                    </div>
                    <div class="grid2">
                        <div class="card">
                            <div class="cardHead">
                                <h2>Simulate a Student Punch</h2>
                            </div>
                            <div class="cardBody">
                                <form id="punchForm" class="formGrid"><select name="device_id" id="punchDevice" required></select><select name="device_user_id" id="punchStudent" required></select><input type="datetime-local" name="punch_time" id="punchTime" required><button class="btn btnPrimary">Send Punch</button></form>
                                <div id="punchResult" class="notice" style="margin-top:14px">Tip: create a session first. Use a time between the session start and end.</div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="cardHead">
                                <h2>Test Absent</h2>
                            </div>
                            <div class="cardBody">
                                <p>Sessions close automatically at their scheduled end time. The system records absent students when closure runs.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="devices" class="view">
                    <div class="welcome">
                        <div>
                            <h1>ZKTeco Devices</h1>
                            <p>Register devices now; later the real device will call the same punch API.</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Register Device</h2>
                        </div>
                        <div class="cardBody">
                            <form class="formGrid" id="deviceForm"><input name="device_code" placeholder="ZK001" required><input name="name" placeholder="ZKTeco Main Device" required><select name="venue_id" id="deviceVenue" required></select><input name="ip_address" placeholder="192.168.1.50"><input name="port" value="4370" type="number"><button class="btn btnPrimary">Register Device</button></form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <h2>Registered Devices</h2>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="devicesTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Venue</th>
                                        <th>IP</th>
                                        <th>Last Seen</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="unmatched" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Unmatched Punches</h1>
                            <p>Punches whose device User ID does not match a student account.</p>
                        </div><button class="btn btnSecondary" data-action="refreshUnmatched">Refresh</button>
                    </div>
                    <div class="card">
                        <div class="tableWrap">
                            <table class="table" id="unmatchedTable">
                                <thead>
                                    <tr>
                                        <th>Device User ID</th>
                                        <th>Device</th>
                                        <th>Punch Time</th>
                                        <th>Received</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="reports" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Reports</h1>
                            <p>Download the current attendance dataset as CSV.</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardBody"><a class="btn btnPrimary" href="api.php?route=admin/reports/attendance.csv">Download Attendance CSV</a>
                            <p class="secondary" style="margin-top:12px">The CSV contains student, course, date, status, source and correction reason.</p>
                        </div>
                    </div>
                </section>

                <section id="audit" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Audit Log</h1>
                            <p>Manual attendance changes and important admin actions are recorded here.</p>
                        </div><button class="btn btnSecondary" data-action="refreshAudit">Refresh</button>
                    </div>
                    <div class="card">
                        <div class="tableWrap">
                            <table class="table" id="auditTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Actor</th>
                                        <th>Action</th>
                                        <th>Entity</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <section id="settings" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Attendance Settings</h1>
                            <p>Change the late window and exam eligibility threshold without editing code.</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardBody">
                            <form id="settingsForm" class="formGrid"><input name="late_window_minutes" id="lateWindow" type="number" min="0" max="240" placeholder="Late window minutes"><input name="eligibility_threshold" id="eligibilityThreshold" type="number" min="0" max="100" placeholder="Eligibility %"><input name="current_semester" id="currentSemester" placeholder="Current semester"><button class="btn btnPrimary">Save Settings</button></form>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <div class="toastBox"></div>
    <script src="js/app.js"></script>
    <script src="js/admin.js"></script>
</body>

</html>