<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FUTO Attendance | Lecturer</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">

</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">F</div>
                <div><b>FUTO ATTENDANCE</b><small>Lecturer Portal</small></div>
            </div>
            <nav class="nav">
                <h6>Teaching</h6><button data-view="dashboard" class="active">⌂ Dashboard</button><button data-view="courses">▦ My Courses</button><button data-view="sessions">◫ Class Sessions</button><button data-view="students">♙ My Students</button><button data-view="attendance">✓ Attendance</button><button data-view="profile">♙ My Profile</button>
            </nav>
            <div class="sidebottom">
                <div class="mini">
                    <div class="avatar">LE</div>
                    <div><b id="sideName">Lecturer</b><small>Lecturer</small></div>
                </div><button class="logout" data-logout>↪ Logout</button>
            </div>
        </aside>
        <main class="main">
            <header class="top">
                <div class="topLeft"><button class="menu">☰</button>
                    <div>
                        <div class="title">Dashboard</div>
                        <div class="sub">Your courses and attendance</div>
                    </div>
                </div>
                <div class="topUser">
                    <div class="avatar">LE</div>
                    <div><b id="topName">Lecturer</b><small>Lecturer</small></div>
                </div>
            </header>
            <div class="content">
                <section id="dashboard" class="view active">
                    <div class="welcome">
                        <div>
                            <h1>Lecturer Dashboard</h1>
                            <p>Monitor your assigned courses and attendance.</p>
                        </div><a class="btn btnPrimary" href="api.php?route=admin/reports/attendance.csv">Download CSV</a>
                    </div>
                    <div class="stats">
                        <div class="stat blue">
                            <div class="statValue" id="lCourses">0</div>
                            <div class="statLabel">My Courses</div>
                        </div>
                        <div class="stat green">
                            <div class="statValue" id="lPresent">0</div>
                            <div class="statLabel">Present Today</div>
                        </div>
                        <div class="stat orange">
                            <div class="statValue" id="lLate">0</div>
                            <div class="statLabel">Late Today</div>
                        </div>
                        <div class="stat purple">
                            <div class="statValue" id="lAbsent">0</div>
                            <div class="statLabel">Absent Today</div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <div>
                                <h2>My Teaching Timetable</h2>
                                <p>Sessions for your assigned courses. Cancelled sessions remain visible.</p>
                            </div><button class="btn btnSecondary" data-action="refreshTimetable">Refresh</button>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="lecturerTimetable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Course</th>
                                        <th>Time</th>
                                        <th>Venue</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div><br>
                    <div class="card">
                        <div class="cardHead">
                            <h2>My Courses</h2>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="myCourses">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Students</th>
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
                            <h1>My Courses</h1>
                            <p>Only courses assigned to you are shown.</p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="tableWrap">
                            <table class="table" id="coursesTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Students</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>
                <section id="sessions" class="view">
                    <div class="welcome"><div><h1>Class Sessions</h1><p>Create sessions for courses assigned to you. Students with an email address will be notified.</p></div><button class="btn btnPrimary" id="showLecturerSession">+ Create Session</button></div>
                    <div class="card hidden" id="lecturerSessionForm"><div class="cardHead"><h2>Schedule a Class</h2></div><div class="cardBody"><form class="formGrid" id="lecturerSessionCreateForm"><select name="course_id" id="lecturerSessionCourse" required></select><select name="venue_id" id="lecturerSessionVenue" required></select><select name="device_id" id="lecturerSessionDevice" required></select><input type="date" name="session_date" required><input type="time" name="start_time" required><input type="time" name="end_time" required><div><button class="btn btnPrimary">Create Session</button> <button class="btn btnSecondary" type="button" id="hideLecturerSession">Cancel</button></div></form></div></div>
                    <div class="card"><div class="tableWrap"><table class="table" id="lecturerSessionsTable"><thead><tr><th>Course</th><th>Date</th><th>Time</th><th>Venue</th><th>Students</th><th>Status</th><th>Action</th></tr></thead><tbody></tbody></table></div></div>
                </section>
                <section id="students" class="view"><div class="welcome"><div><h1>My Students</h1><p>Students enrolled in your assigned courses and their attendance progress.</p></div><button class="btn btnSecondary" id="refreshLecturerStudents">Refresh</button></div><div class="card"><div class="tableWrap"><table class="table" id="lecturerStudentsTable"><thead><tr><th>Course</th><th>Student</th><th>Matric No.</th><th>Email</th><th>Attendance</th><th>Classes attended</th></tr></thead><tbody></tbody></table></div></div></section>
                <section id="attendance" class="view">
                    <div class="welcome">
                        <div>
                            <h1>Attendance</h1>
                            <p>You can correct attendance for your own courses. A reason is required.</p>
                        </div>
                        <div class="actions"><button class="btn btnSecondary" data-action="refresh">Refresh</button><a class="btn btnPrimary" href="api.php?route=admin/reports/attendance.csv">Download CSV</a></div>
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
                <section id="profile" class="view">
                    <div class="welcome">
                        <div>
                            <h1>My Profile</h1>
                            <p>Update your contact details and password.</p>
                        </div>
                    </div>
                    <div class="grid2">
                        <div class="card">
                            <div class="cardBody">
                                <form id="profileForm" class="formGrid"><input id="profileEmail" name="email" placeholder="Email"><input id="profilePhone" name="phone" placeholder="Phone"><button class="btn btnPrimary">Save Changes</button></form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="cardBody">
                                <form id="passwordForm" class="formGrid"><input name="current_password" type="password" placeholder="Current password" required><input name="new_password" type="password" placeholder="New password" required><button class="btn btnPrimary">Update Password</button></form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <div class="toastBox"></div>
    <script src="js/app.js"></script>
    <script src="js/lecturer.js"></script>
</body>

</html>