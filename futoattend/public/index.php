<?php
// Main entry: FUTO Attendance login page.
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>FUTO Attendance | Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef5ff, #fff)
        }

        .login {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr
        }

        .hero {
            background: linear-gradient(145deg, #0b1f3a, #102f59 70%, #1455a0);
            color: #fff;
            padding: clamp(35px, 7vw, 100px);
            display: flex;
            flex-direction: column;
            justify-content: center
        }

        .heroMark {
            width: 58px;
            height: 58px;
            border-radius: 17px;
            background: #fff;
            color: #0b1f3a;
            display: grid;
            place-items: center;
            font-size: 25px;
            font-weight: 900;
            margin-bottom: 28px
        }

        .eyebrow {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9fc0e8;
            font-weight: 800
        }

        .hero h1 {
            font-size: clamp(34px, 4.5vw, 60px);
            line-height: 1.05;
            letter-spacing: -1.5px;
            margin: 10px 0 18px
        }

        .hero p {
            color: #c3d2e7;
            max-width: 510px;
            font-size: 14px
        }

        .points {
            display: grid;
            gap: 10px;
            margin-top: 18px;
            font-size: 11px
        }

        .loginWrap {
            display: grid;
            place-items: center;
            padding: 30px
        }

        .loginCard {
            width: min(470px, 100%);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 60px #0b1f3a1a
        }

        .loginCard h2 {
            margin: 0 0 3px;
            font-size: 25px
        }

        .loginCard>p {
            margin: 0 0 23px;
            color: var(--muted);
            font-size: 11px
        }

        .userIcon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--soft);
            color: var(--blue);
            display: grid;
            place-items: center;
            font-size: 23px;
            margin-bottom: 17px
        }

        .fg {
            margin-bottom: 14px
        }

        .input {
            position: relative
        }

        .input input {
            padding-left: 12px;
            padding-right: 40px;
            min-height: 45px
        }

        .eye {
            position: absolute;
            right: 6px;
            top: 5px;
            width: 34px;
            height: 34px;
            border: 0;
            background: transparent;
            color: var(--muted)
        }

        #loginMsg {
            min-height: 18px;
            margin-top: 8px;
            font-size: 10px
        }

        #loginMsg.error {
            color: var(--red)
        }

        #loginMsg.ok {
            color: var(--green)
        }

        .demo {
            margin-top: 19px;
            background: #f7faff;
            border: 1px dashed #cbdcf4;
            border-radius: 11px;
            padding: 12px
        }

        .demo b {
            font-size: 10px
        }

        .demoRow {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 7px;
            font-size: 9px
        }

        .demoRow span {
            width: 58px;
            font-weight: 800
        }

        .demoRow code {
            flex: 1;
            color: #617089;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .demoRow button {
            border: 0;
            background: #fff;
            color: var(--blue);
            font-weight: 800;
            font-size: 9px;
            padding: 4px 7px;
            border-radius: 6px
        }

        .foot {
            text-align: center;
            color: #8a95a6;
            font-size: 9px;
            margin-top: 14px
        }

        @media(max-width:850px) {
            .login {
                grid-template-columns: 1fr
            }

            .hero {
                min-height: 280px;
                padding: 38px 25px 70px
            }

            .hero h1 {
                font-size: 36px
            }

            .points {
                display: none
            }

            .loginWrap {
                margin-top: -45px;
                padding: 0 14px 30px;
                position: relative
            }

            .loginCard {
                border-radius: 16px
            }
        }

        @media(max-width:500px) {
            .hero {
                min-height: 225px;
                padding: 25px 18px 60px
            }

            .heroMark {
                width: 45px;
                height: 45px;
                margin-bottom: 16px
            }

            .hero h1 {
                font-size: 29px
            }

            .loginCard {
                padding: 22px 17px
            }
        }
    </style>
</head>

<body>
    <main class="login">
        <section class="hero">
            <div class="heroMark">F</div>
            <div class="eyebrow">Federal University of Technology, Owerri</div>
            <h1>Web Attendance<br>Management System</h1>
            <p>One secure website for students, lecturers and administrators.</p>
            <div class="points"><span>✓ ZKTeco attendance integration</span><span>✓ Course and session management</span><span>✓ Attendance & exam eligibility</span></div>
        </section>
        <section class="loginWrap">
            <div>
                <div class="loginCard">
                    <div class="userIcon">♙</div>
                    <h2>Welcome back</h2>
                    <p>Enter your credentials to continue</p>
                    <form id="loginForm">
                        <div class="fg"><label for="loginId">Username / ID</label><input id="loginId" placeholder="Matric No. / Staff ID / Admin ID" required></div>
                        <div class="fg"><label for="password">Password</label>
                            <div class="input"><input id="password" type="password" placeholder="Enter your password" required><button class="eye" type="button" data-showpw>◉</button></div>
                        </div><button class="btn btnPrimary btnBlock" style="min-height:45px" type="submit">Login →</button>
                        <div id="loginMsg"></div>
                    </form>
                    <div class="demo"><b>MVP demo accounts</b>
                        <div class="demoRow"><span>Student</span><code>1</code><button data-demo="student">Use</button></div>
                        <div class="demoRow"><span>Lecturer</span><code>STAFF/CSC/001</code><button data-demo="lecturer">Use</button></div>
                        <div class="demoRow"><span>Admin</span><code>ADMIN001</code><button data-demo="admin">Use</button></div><small style="font-size:8px;color:var(--muted);display:block;margin-top:7px">Demo password: <b>password</b></small>
                    </div>
                </div>
                <div class="foot">© 2026 FUTO Attendance Management System</div>
            </div>
        </section>
    </main>
    <div class="toastBox"></div>
    <script src="js/app.js"></script>
    <script src="js/login.js"></script>
</body>

</html>