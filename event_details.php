<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/database.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid event ID.");
}

$event_id = intval($_GET["id"]);
$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


/* ==============================
   JOIN EVENT
================================ */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* Check if already registered */

    $check_sql = "SELECT registration_id
                  FROM event_registrations
                  WHERE user_id = ? AND event_id = ?";

    $check_stmt = $conn->prepare($check_sql);

    if (!$check_stmt) {
        die("Database error: " . $conn->error);
    }

    $check_stmt->bind_param(
        "ii",
        $user_id,
        $event_id
    );

    $check_stmt->execute();

    $check_result = $check_stmt->get_result();


    if ($check_result->num_rows > 0) {

        $message = "You have already joined this event.";
        $message_type = "warning";

    } else {

        /* Get event capacity */

        $capacity_sql = "SELECT capacity
                         FROM events
                         WHERE event_id = ?";

        $capacity_stmt = $conn->prepare($capacity_sql);

        $capacity_stmt->bind_param(
            "i",
            $event_id
        );

        $capacity_stmt->execute();

        $capacity_result = $capacity_stmt->get_result();

        $capacity_data = $capacity_result->fetch_assoc();

        $capacity_stmt->close();


        if (!$capacity_data) {

            $message = "Event not found.";
            $message_type = "error";

        } else {

            $capacity = intval(
                $capacity_data["capacity"]
            );


            /* Count current registrations */

            $count_sql = "SELECT COUNT(*) AS total
                          FROM event_registrations
                          WHERE event_id = ?";

            $count_stmt = $conn->prepare($count_sql);

            $count_stmt->bind_param(
                "i",
                $event_id
            );

            $count_stmt->execute();

            $count_result = $count_stmt->get_result();

            $count_data = $count_result->fetch_assoc();

            $registered_count = intval(
                $count_data["total"]
            );

            $count_stmt->close();


            /* Check capacity */

            if ($registered_count >= $capacity) {

                $message = "Sorry, this event is full.";
                $message_type = "error";

            } else {

                /* Register student */

                $join_sql = "INSERT INTO event_registrations
                             (user_id, event_id)
                             VALUES (?, ?)";

                $join_stmt = $conn->prepare($join_sql);

                if (!$join_stmt) {
                    die("Database error: " . $conn->error);
                }

                $join_stmt->bind_param(
                    "ii",
                    $user_id,
                    $event_id
                );


                if ($join_stmt->execute()) {

                    $message = "Successfully joined the event! 🎉";
                    $message_type = "success";

                } else {

                    $message = "Failed to join the event.";
                    $message_type = "error";

                }

                $join_stmt->close();
            }
        }
    }

    $check_stmt->close();
}


/* ==============================
   GET EVENT DETAILS
================================ */

$sql = "SELECT
            e.event_id,
            e.title,
            e.description,
            e.event_date,
            e.event_time,
            e.venue,
            e.capacity,
            e.image,
            c.category_name
        FROM events e
        LEFT JOIN categories c
            ON e.category_id = c.category_id
        WHERE e.event_id = ?";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $event_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows == 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();

$stmt->close();


/* ==============================
   REGISTERED COUNT
================================ */

$count_sql = "SELECT COUNT(*) AS total
              FROM event_registrations
              WHERE event_id = ?";

$count_stmt = $conn->prepare($count_sql);

$count_stmt->bind_param(
    "i",
    $event_id
);

$count_stmt->execute();

$count_result = $count_stmt->get_result();

$count_data = $count_result->fetch_assoc();

$registered_count = intval(
    $count_data["total"]
);

$count_stmt->close();


/* ==============================
   AVAILABLE SEATS
================================ */

$capacity = intval(
    $event["capacity"]
);

$available_seats = $capacity - $registered_count;

if ($available_seats < 0) {
    $available_seats = 0;
}


/* ==============================
   CHECK CURRENT USER
================================ */

$user_check_sql = "SELECT registration_id
                   FROM event_registrations
                   WHERE user_id = ?
                   AND event_id = ?";

$user_check_stmt = $conn->prepare(
    $user_check_sql
);

$user_check_stmt->bind_param(
    "ii",
    $user_id,
    $event_id
);

$user_check_stmt->execute();

$user_check_result =
    $user_check_stmt->get_result();

$already_joined =
    $user_check_result->num_rows > 0;

$user_check_stmt->close();

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
    <?php echo htmlspecialchars($event["title"]); ?>
    - NSBM EventHub
</title>


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


/* Header */

.header {

    background: #17365d;

    color: white;

    padding: 20px;

    text-align: center;
}

.header h1 {

    margin: 0;
}


/* Container */

.container {

    width: 90%;

    max-width: 850px;

    margin: 40px auto;
}


/* Event Box */

.event-box {

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.1);
}


/* Image */

.event-image {

    width: 100%;

    height: 320px;

    background: #dfe6ed;

    display: flex;

    justify-content: center;

    align-items: center;

    margin-bottom: 25px;

    border-radius: 10px;

    overflow: hidden;

    color: #666;
}

.event-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}


/* Title */

.event-title {

    color: #17365d;

    font-size: 30px;

    margin-bottom: 20px;
}


/* Info */

.info {

    margin: 15px 0;

    color: #444;

    font-size: 16px;
}


/* Category */

.category {

    display: inline-block;

    background: #17365d;

    color: white;

    padding: 7px 14px;

    border-radius: 20px;

    margin-bottom: 15px;

    font-size: 14px;
}


/* Capacity Box */

.capacity-box {

    margin-top: 25px;

    padding: 20px;

    background: #f4f6f8;

    border-radius: 8px;
}

.capacity-box h3 {

    margin-top: 0;

    color: #17365d;
}


/* Available */

.available {

    color: #28a745;

    font-weight: bold;
}


/* Full */

.full {

    color: #d9534f;

    font-weight: bold;
}


/* Message */

.message {

    margin: 20px 0;

    padding: 15px;

    border-radius: 6px;

    text-align: center;

    font-weight: bold;
}

.success {

    background: #d4edda;

    color: #155724;
}

.warning {

    background: #fff3cd;

    color: #856404;
}

.error {

    background: #f8d7da;

    color: #721c24;
}


/* Join Button */

.join-button {

    margin-top: 20px;

    padding: 13px 25px;

    background: #28a745;

    color: white;

    border: none;

    border-radius: 6px;

    cursor: pointer;

    font-size: 16px;

    font-weight: bold;
}

.join-button:hover {

    opacity: 0.9;
}


/* Disabled Button */

.disabled-button {

    margin-top: 20px;

    padding: 13px 25px;

    background: #999;

    color: white;

    border: none;

    border-radius: 6px;

    font-size: 16px;

    cursor: not-allowed;
}


/* Back */

.back-button {

    display: inline-block;

    margin-top: 20px;

    padding: 12px 20px;

    background: #17365d;

    color: white;

    text-decoration: none;

    border-radius: 6px;
}

.back-button:hover {

    opacity: 0.9;
}


/* Description */

.description {

    line-height: 1.7;

    color: #444;
}


/* Mobile */

@media (max-width: 600px) {

    .container {

        width: 95%;
    }

    .event-box {

        padding: 20px;
    }

    .event-image {

        height: 220px;
    }

    .event-title {

        font-size: 24px;
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
        Event Details
    </p>

</div>


<div class="container">


<div class="event-box">


<!-- Event Image -->

<div class="event-image">

<?php if (!empty($event["image"])): ?>

    <img
        src="image/<?php
        echo htmlspecialchars($event["image"]);
        ?>"
        alt="Event Image"
    >

<?php else: ?>

    <span>
        No Image Available
    </span>

<?php endif; ?>

</div>


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

<h2 class="event-title">

    <?php
    echo htmlspecialchars(
        $event["title"]
    );
    ?>

</h2>


<!-- Date -->

<p class="info">

    📅

    <strong>
        Date:
    </strong>

    <?php

    echo date(
        "d F Y",
        strtotime($event["event_date"])
    );

    ?>

</p>


<!-- Time -->

<p class="info">

    🕐

    <strong>
        Time:
    </strong>

    <?php

    echo date(
        "h:i A",
        strtotime($event["event_time"])
    );

    ?>

</p>


<!-- Venue -->

<p class="info">

    📍

    <strong>
        Venue:
    </strong>

    <?php

    echo htmlspecialchars(
        $event["venue"]
    );

    ?>

</p>


<hr>


<!-- Description -->

<h3>
    Description
</h3>

<p class="description">

    <?php

    echo nl2br(
        htmlspecialchars(
            $event["description"]
        )
    );

    ?>

</p>


<!-- Capacity -->

<div class="capacity-box">

    <h3>
        👥 Event Capacity
    </h3>

    <p>
        Total Capacity:
        <strong>
            <?php echo $capacity; ?>
        </strong>
    </p>

    <p>
        Registered:
        <strong>
            <?php echo $registered_count; ?>
        </strong>
    </p>

    <?php if ($available_seats > 0): ?>

        <p class="available">

            🟢
            Available Seats:
            <?php echo $available_seats; ?>

        </p>

    <?php else: ?>

        <p class="full">

            🔴
            Event Full

        </p>

    <?php endif; ?>

</div>


<!-- Message -->

<?php if (!empty($message)): ?>

    <div class="message <?php echo $message_type; ?>">

        <?php
        echo htmlspecialchars(
            $message
        );
        ?>

    </div>

<?php endif; ?>


<!-- Join Button -->

<?php if ($already_joined): ?>

    <button
        class="disabled-button"
        disabled
    >
        ✅ Already Registered
    </button>


<?php elseif ($available_seats <= 0): ?>

    <button
        class="disabled-button"
        disabled
    >
        🔴 Event Full
    </button>


<?php else: ?>

    <form method="POST">

        <button
            type="submit"
            class="join-button"
        >
            🎉 Join This Event
        </button>

    </form>

<?php endif; ?>


<br>


<a
    class="back-button"
    href="dashboard.php"
>
    ← Back to Events
</a>


</div>

</div>

</body>

</html>
