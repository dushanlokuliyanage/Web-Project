<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] != "admin"
) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/database.php";


/* =========================
   DASHBOARD COUNTS
========================= */

$event_result = $conn->query(
    "SELECT COUNT(*) AS total FROM events"
);

$event_count =
    $event_result->fetch_assoc()["total"];


$student_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);

$student_count =
    $student_result->fetch_assoc()["total"];


$registration_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM event_registrations"
);

$registration_count =
    $registration_result->fetch_assoc()["total"];


/* Upcoming events */

$upcoming_result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date >= CURDATE()"
);

$upcoming_count =
    $upcoming_result->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<link rel="icon" type="image/png" href="favicon.png">

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Admin Dashboard - NSBM EventHub
</title>


<style>

/* =========================
   GENERAL
========================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f7fb;

    color: #333;
}


/* =========================
   NAVBAR
========================= */

.navbar {

    background: #17365d;

    color: white;

    padding: 16px 5%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,0.15);
}

.logo {

    font-size: 24px;

    font-weight: bold;
}

.logo span {

    color: #5bc0de;
}


.nav-links {

    display: flex;

    align-items: center;

    gap: 8px;
}

.nav-links a {

    color: white;

    text-decoration: none;

    padding: 9px 13px;

    border-radius: 6px;

    font-size: 14px;
}

.nav-links a:hover {

    background:
        rgba(255,255,255,0.15);
}

.logout {

    background:
        #d9534f !important;
}

.logout:hover {

    background:
        #c9302c !important;
}


/* =========================
   HERO
========================= */

.hero {

    background:
        linear-gradient(
            135deg,
            #17365d,
            #245b91
        );

    color: white;

    padding: 42px 5%;

    text-align: center;
}

.hero h1 {

    margin: 0 0 10px;

    font-size: 32px;
}

.hero p {

    margin: 0;

    font-size: 16px;

    opacity: 0.9;
}


/* =========================
   CONTAINER
========================= */

.container {

    width: 90%;

    max-width: 1200px;

    margin: 35px auto;
}


/* =========================
   WELCOME
========================= */

.welcome {

    background: white;

    padding: 25px;

    border-radius: 12px;

    margin-bottom: 25px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.07);
}

.welcome h2 {

    margin-top: 0;

    color: #17365d;
}

.welcome p {

    color: #666;
}


/* =========================
   STATISTICS
========================= */

.stats {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(220px, 1fr)
        );

    gap: 20px;

    margin-bottom: 35px;
}

.stat-card {

    background: white;

    padding: 25px;

    border-radius: 12px;

    text-align: center;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.07);

    transition:
        transform 0.2s ease;
}

.stat-card:hover {

    transform:
        translateY(-4px);
}

.stat-icon {

    font-size: 32px;

    margin-bottom: 8px;
}

.stat-card h3 {

    margin: 5px 0;

    color: #666;

    font-size: 15px;
}

.stat-number {

    color: #17365d;

    font-size: 34px;

    font-weight: bold;

    margin: 8px 0;
}


/* =========================
   SECTION TITLE
========================= */

.section-title {

    color: #17365d;

    margin-bottom: 20px;
}


/* =========================
   MENU
========================= */

.menu {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(250px, 1fr)
        );

    gap: 22px;
}

.menu-card {

    background: white;

    padding: 25px;

    border-radius: 12px;

    text-align: center;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.07);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.menu-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 8px 20px
        rgba(0,0,0,0.12);
}

.menu-icon {

    font-size: 38px;

    margin-bottom: 10px;
}

.menu-card h3 {

    color: #17365d;

    margin: 8px 0;
}

.menu-card p {

    color: #666;

    line-height: 1.5;

    min-height: 45px;
}


/* =========================
   BUTTONS
========================= */

.button {

    display: inline-block;

    padding: 11px 20px;

    color: white;

    text-decoration: none;

    border-radius: 7px;

    margin-top: 12px;

    font-weight: bold;
}

.button:hover {

    opacity: 0.85;
}

.add {

    background: #28a745;
}

.manage {

    background: #17365d;
}

.registration {

    background: #6f42c1;
}

.users {

    background: #00897b;
}

.home {

    background: #f0ad4e;
}


/* =========================
   FOOTER
========================= */

.footer {

    margin-top: 50px;

    background: #17365d;

    color: white;

    text-align: center;

    padding: 20px;

    font-size: 14px;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

    .navbar {

        flex-direction: column;

        gap: 12px;

        text-align: center;
    }

    .nav-links {

        flex-wrap: wrap;

        justify-content: center;
    }

    .hero h1 {

        font-size: 26px;
    }

    .container {

        width: 94%;
    }

}

</style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <div class="logo">

        NSBM
        <span>EventHub</span>

    </div>


    <div class="nav-links">

        <a href="dashboard.php">
            🏠 Dashboard
        </a>

        <a href="add_event.php">
            ➕ Add Event
        </a>

        <a href="manage_events.php">
            📅 Events
        </a>

        <a href="registrations.php">
            📋 Registrations
        </a>

        <a href="users.php">
            👥 Users
        </a>

        <a
            class="logout"
            href="logout.php"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     HERO
========================= -->

<section class="hero">

    <h1>
        Admin Dashboard ⚙️
    </h1>

    <p>
        Manage events, students and registrations from one place.
    </p>

</section>


<div class="container">


<!-- =========================
     WELCOME
========================= -->

<div class="welcome">

    <h2>
        Welcome, Admin! 👋
    </h2>

    <p>
        Manage the NSBM EventHub platform and keep students updated with upcoming events.
    </p>

</div>


<!-- =========================
     STATISTICS
========================= -->

<h2 class="section-title">
    📊 Dashboard Overview
</h2>


<div class="stats">


<div class="stat-card">

    <div class="stat-icon">
        🎫
    </div>

    <h3>
        Total Events
    </h3>

    <div class="stat-number">

        <?php
        echo $event_count;
        ?>

    </div>

</div>


<div class="stat-card">

    <div class="stat-icon">
        📅
    </div>

    <h3>
        Upcoming Events
    </h3>

    <div class="stat-number">

        <?php
        echo $upcoming_count;
        ?>

    </div>

</div>


<div class="stat-card">

    <div class="stat-icon">
        👨‍🎓
    </div>

    <h3>
        Total Students
    </h3>

    <div class="stat-number">

        <?php
        echo $student_count;
        ?>

    </div>

</div>


<div class="stat-card">

    <div class="stat-icon">
        📋
    </div>

    <h3>
        Registrations
    </h3>

    <div class="stat-number">

        <?php
        echo $registration_count;
        ?>

    </div>

</div>


</div>


<!-- =========================
     MANAGEMENT
========================= -->

<h2 class="section-title">
    🛠️ Management
</h2>


<div class="menu">


<!-- Add Event -->

<div class="menu-card">

    <div class="menu-icon">
        ➕
    </div>

    <h3>
        Add New Event
    </h3>

    <p>
        Create a new event with date, time, venue, capacity and image.
    </p>

    <a
        class="button add"
        href="add_event.php"
    >
        Add Event
    </a>

</div>


<!-- Manage Events -->

<div class="menu-card">

    <div class="menu-icon">
        📅
    </div>

    <h3>
        Manage Events
    </h3>

    <p>
        View, edit or delete existing NSBM events.
    </p>

    <a
        class="button manage"
        href="manage_events.php"
    >
        Manage Events
    </a>

</div>


<!-- Registrations -->

<div class="menu-card">

    <div class="menu-icon">
        📋
    </div>

    <h3>
        Event Registrations
    </h3>

    <p>
        View students registered for each event.
    </p>

    <a
        class="button registration"
        href="registrations.php"
    >
        View Registrations
    </a>

</div>


<!-- Users -->

<div class="menu-card">

    <div class="menu-icon">
        👥
    </div>

    <h3>
        User Management
    </h3>

    <p>
        View registered students and system users.
    </p>

    <a
        class="button users"
        href="users.php"
    >
        View Users
    </a>

</div>


<!-- Website -->

<div class="menu-card">

    <div class="menu-icon">
        🌐
    </div>

    <h3>
        Website Home
    </h3>

    <p>
        Visit the main NSBM EventHub website.
    </p>

    <a
        class="button home"
        href="../index.php"
    >
        Visit Website
    </a>

</div>


</div>


</div>


<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <p>
        © 2026 NSBM EventHub
    </p>

    <p>
        Admin Management System
    </p>

</footer>


</body>

</html>
