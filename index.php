<?php
session_start();

require_once __DIR__ . "/database.php";

$user_initials = "";

if (isset($_SESSION["user_id"])) {

    $name_parts = preg_split(
        "/\\s+/",
        trim($_SESSION["name"] ?? "")
    );

    $user_initials = strtoupper(
        substr($name_parts[0], 0, 1)
    );

    if (count($name_parts) > 1) {
        $user_initials .= strtoupper(
            substr($name_parts[count($name_parts) - 1], 0, 1)
        );
    }
}

$sql = "SELECT event_id, title, description, event_date, event_time, venue, image
        FROM events
        WHERE event_date >= CURDATE()
        ORDER BY event_date ASC, event_time ASC";

$result = $conn->query($sql);

if ($result === false) {
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>NSBM EventHub</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
        }

        .navbar {
            background: #17365d;
            color: white;
            padding: 18px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        .user-initials {
            display: inline-flex;
            width: 32px;
            height: 32px;
            margin-left: 20px;
            background: white;
            color: #17365d;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .hero {
            background: #17365d;
            color: white;
            text-align: center;
            padding: 70px 20px;
        }

        .hero h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 18px;
        }

        .join-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .section-title {
            text-align: center;
            color: #17365d;
            margin-bottom: 30px;
        }

        .events {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(280px, 1fr)
            );
            gap: 25px;
        }

        .event-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .event-image {
            height: 180px;
            background: #dfe6ed;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #666;
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-content {
            padding: 20px;
        }

        .event-content h3 {
            color: #17365d;
            margin-top: 0;
        }

        .info {
            color: #555;
            margin: 8px 0;
        }

        .view-button {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 16px;
            background: #17365d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .no-events {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }

        footer {
            background: #17365d;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
        }

    </style>

</head>

<body>


<!-- Navigation -->

<div class="navbar">

    <div class="logo">
        NSBM EventHub
    </div>

    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="my_events.php">
                My Event
            </a>

            <span class="user-initials">
                <?php echo htmlspecialchars($user_initials); ?>
            </span>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

        <?php endif; ?>

    </div>

</div>


<!-- Hero -->

<section class="hero">

    <h1>
        Welcome to NSBM EventHub
    </h1>

    <p>
        Discover, manage and participate in exciting events happening at NSBM.
    </p>

    <a
        class="join-button"
        href="register.php"
    >
        Join EventHub
    </a>

</section>


<!-- Upcoming Events -->

<div class="container">

    <h2 class="section-title">
        Upcoming Events
    </h2>


    <?php if ($result->num_rows > 0): ?>

        <div class="events">

            <?php while ($event = $result->fetch_assoc()): ?>

                <div class="event-card">


                    <div class="event-image">

                        <?php if (!empty($event["image"])): ?>

                            <img
                                src="image/<?php echo htmlspecialchars($event["image"]); ?>"
                                alt="Event Image"
                            >

                        <?php else: ?>

                            No Image

                        <?php endif; ?>

                    </div>


                    <div class="event-content">

                        <h3>
                            <?php echo htmlspecialchars($event["title"]); ?>
                        </h3>

                        <p class="info">
                            📅
                            <?php
                            echo date(
                                "d F Y",
                                strtotime($event["event_date"])
                            );
                            ?>
                        </p>

                        <p class="info">
                            🕐
                            <?php
                            echo date(
                                "h:i A",
                                strtotime($event["event_time"])
                            );
                            ?>
                        </p>

                        <p class="info">
                            📍
                            <?php echo htmlspecialchars($event["venue"]); ?>
                        </p>

                        <p>
                            <?php
                            echo htmlspecialchars(
                                $event["description"]
                            );
                            ?>
                        </p>

                        <a
                            class="view-button"
                            href="event_details.php?id=<?php echo htmlspecialchars($event["event_id"]); ?>"
                        >
                            View Event
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>


    <?php else: ?>

        <div class="no-events">

            <h3>
                No upcoming events available yet.
            </h3>

            <p>
                Please check again later.
            </p>

        </div>

    <?php endif; ?>

</div>


<!-- Footer -->

<footer>

    © 2026 NSBM EventHub | All Rights Reserved

</footer>

</body>

</html>
