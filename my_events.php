<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/database.php";

$user_id = $_SESSION["user_id"];

$sql = "SELECT
            e.event_id,
            e.title,
            e.description,
            e.event_date,
            e.event_time,
            e.venue,
            e.capacity,
            e.image,
            c.category_name,
            er.registered_at

        FROM event_registrations er

        INNER JOIN events e
            ON er.event_id = e.event_id

        LEFT JOIN categories c
            ON e.category_id = c.category_id

        WHERE er.user_id = ?

        ORDER BY e.event_date ASC,
                 e.event_time ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    My Registered Events - NSBM EventHub
</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f8;
}


/* Header */

.header {

    background: #17365d;

    color: white;

    padding: 25px;

    text-align: center;
}

.header h1 {

    margin: 0;
}


/* Container */

.container {

    width: 90%;

    max-width: 1100px;

    margin: 40px auto;
}


/* Event Grid */

.event-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(280px, 1fr)
        );

    gap: 25px;
}


/* Event Card */

.event-card {

    background: white;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.1);
}


/* Image */

.event-image {

    width: 100%;

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


/* Content */

.event-content {

    padding: 20px;
}

.event-content h3 {

    color: #17365d;

    margin-top: 0;
}


/* Category */

.category {

    display: inline-block;

    background: #17365d;

    color: white;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 13px;

    margin-bottom: 12px;
}


/* Info */

.info {

    margin: 9px 0;

    color: #555;
}


/* Registered */

.registered {

    margin-top: 15px;

    padding: 10px;

    background: #e8f5e9;

    color: #2e7d32;

    border-radius: 5px;

    font-size: 14px;
}


/* Buttons */

.view-button,
.back-button {

    display: inline-block;

    padding: 10px 15px;

    margin-top: 15px;

    background: #17365d;

    color: white;

    text-decoration: none;

    border-radius: 5px;
}

.view-button:hover,
.back-button:hover {

    opacity: 0.9;
}


/* Empty */

.empty {

    background: white;

    padding: 40px;

    text-align: center;

    border-radius: 10px;
}


/* Mobile */

@media (max-width: 600px) {

    .container {

        width: 95%;
    }

}

</style>

</head>


<body>


<div class="header">

    <h1>
        NSBM EventHub
    </h1>

    <p>
        My Registered Events
    </p>

</div>


<div class="container">


<?php if ($result->num_rows > 0): ?>


    <div class="event-grid">


        <?php while ($event = $result->fetch_assoc()): ?>


            <div class="event-card">


                <!-- Image -->

                <div class="event-image">

                    <?php if (!empty($event["image"])): ?>

                        <img
                            src="image/<?php
                            echo htmlspecialchars(
                                $event["image"]
                            );
                            ?>"
                            alt="Event Image"
                        >

                    <?php else: ?>

                        No Image

                    <?php endif; ?>

                </div>


                <div class="event-content">


                    <!-- Category -->

                    <?php if (!empty($event["category_name"])): ?>

                        <span class="category">

                            🏷️

                            <?php
                            echo htmlspecialchars(
                                $event["category_name"]
                            );
                            ?>

                        </span>

                    <?php endif; ?>


                    <!-- Title -->

                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $event["title"]
                        );
                        ?>

                    </h3>


                    <!-- Date -->

                    <p class="info">

                        📅

                        <?php

                        echo date(
                            "d F Y",
                            strtotime(
                                $event["event_date"]
                            )
                        );

                        ?>

                    </p>


                    <!-- Time -->

                    <p class="info">

                        🕐

                        <?php

                        echo date(
                            "h:i A",
                            strtotime(
                                $event["event_time"]
                            )
                        );

                        ?>

                    </p>


                    <!-- Venue -->

                    <p class="info">

                        📍

                        <?php

                        echo htmlspecialchars(
                            $event["venue"]
                        );

                        ?>

                    </p>


                    <!-- Description -->

                    <p>

                        <?php

                        echo htmlspecialchars(
                            $event["description"]
                        );

                        ?>

                    </p>


                    <!-- Capacity -->

                    <p class="info">

                        👥 Capacity:

                        <strong>

                            <?php
                            echo intval(
                                $event["capacity"]
                            );
                            ?>

                        </strong>

                    </p>


                    <!-- Registered Date -->

                    <div class="registered">

                        ✅ Registered on:

                        <?php

                        echo date(
                            "d F Y, h:i A",
                            strtotime(
                                $event["registered_at"]
                            )
                        );

                        ?>

                    </div>


                    <!-- View -->

                    <a
                        class="view-button"
                        href="event_details.php?id=<?php
                        echo $event["event_id"];
                        ?>"
                    >
                        👀 View Event
                    </a>


                </div>

            </div>


        <?php endwhile; ?>


    </div>


<?php else: ?>


    <div class="empty">

        <h3>
            You have not joined any events yet.
        </h3>

        <p>
            Explore upcoming events and join one!
        </p>

        <a
            class="back-button"
            href="dashboard.php"
        >
            ← Explore Events
        </a>

    </div>


<?php endif; ?>


<br>


<a
    class="back-button"
    href="dashboard.php"
>
    ← Back to Dashboard
</a>


</div>


</body>

</html>
