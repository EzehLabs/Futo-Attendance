<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FUTO Attendance | Student</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">

</head>

<body>
    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">F</div>
                <div><b>FUTO ATTENDANCE</b><small>Student Portal</small></div>
            </div>
            <nav class="nav">
                <h6>My Attendance</h6><button data-view="dashboard" class="active">⌂ Dashboard</button><button data-view="attendance">✓ Attendance History</button><button data-view="profile">♙ My Profile</button>
            </nav>
            <div class="sidebottom">
                <div class="mini">
                    <div class="avatar">ST</div>
                    <div><b id="sideName">Student</b><small>Student</small></div>
                </div><button class="logout" data-logout>↪ Logout</button>
            </div>
        </aside>
        <main class="main">
            <header class="top">
                <div class="topLeft"><button class="menu">☰</button>
                    <div>
                        <div class="title">Dashboard</div>
                        <div class="sub">Your attendance and eligibility</div>
                    </div>
                </div>
                <div class="topUser">
                    <div class="avatar">ST</div>
                    <div><b id="topName">Student</b><small>Student</small></div>
                </div>
            </header>
            <div class="content">
                <section id="dashboard" class="view active">
                    <div class="welcome">
                        <div>
                            <h1>My Attendance</h1>
                            <p>Only your own attendance records are shown here.</p>
                        </div><button class="btn btnSecondary" data-action="refresh">Refresh</button>
                    </div>
                    <div class="stats">
                        <div class="stat blue">
                            <div class="statValue" id="sCourses">0</div>
                            <div class="statLabel">Registered Courses</div>
                        </div>
                        <div class="stat green">
                            <div class="statValue" id="sPresent">0</div>
                            <div class="statLabel">Present Today</div>
                        </div>
                        <div class="stat orange">
                            <div class="statValue" id="sLate">0</div>
                            <div class="statLabel">Late Today</div>
                        </div>
                        <div class="stat purple">
                            <div class="statValue" id="sAbsent">0</div>
                            <div class="statLabel">Absent Today</div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cardHead">
                            <div>
                                <h2>My Timetable</h2>
                                <p>Classes for your enrolled courses. Cancelled sessions remain visible.</p>
                            </div><button class="btn btnSecondary" data-action="refreshTimetable">Refresh</button>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="studentTimetable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Course</th>
                                        <th>Time</th>
                                        <th>Venue</th>
                                        <th>Lecturer</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div><br>
                    <div class="card">
                        <div class="cardHead">
                            <div>
                                <h2>Course Attendance</h2>
                                <p id="thresholdText">Eligibility threshold: 75%</p>
                            </div>
                        </div>
                        <div class="tableWrap">
                            <table class="table" id="courseTable">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Attendance</th>
                                        <th>Progress</th>
                                        <th>Eligibility</th>
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
                            <h1>Attendance History</h1>
                            <p>Every closed class contributes to the percentage.</p>
                        </div><button class="btn btnSecondary" data-action="refresh">Refresh</button>
                    </div>
                    <div class="card">
                        <div class="tableWrap">
                            <table class="table" id="historyTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Course</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Source</th>
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
                            <p>You may update your email and phone.</p>
                        </div>
                    </div>
                    <div class="grid2">
                        <div class="card">
                            <div class="cardHead">
                                <h2>Contact Information</h2>
                            </div>
                            <div class="cardBody">
                                <form id="profileForm" class="formGrid"><input id="profileEmail" name="email" placeholder="Email"><input id="profilePhone" name="phone" placeholder="Phone"><button class="btn btnPrimary">Save Changes</button></form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="cardHead">
                                <h2>Change Password</h2>
                            </div>
                            <div class="cardBody">
                                <form id="passwordForm" class="formGrid"><input name="current_password" type="password" placeholder="Current password" required><input name="new_password" type="password" placeholder="New password (6+ chars)" required><button class="btn btnPrimary">Update Password</button></form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <div class="toastBox"></div>
    <script src="js/app.js"></script>
    <script src="js/student.js"></script>
</body>

</html>