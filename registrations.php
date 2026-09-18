<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/database.php";


$sql = "SELECT
            event_registrations.registration_id,
            users.name,
            users.email,
            events.title,
            events.event_date,
            events.event_time,
            events.venue,
            event_registrations.registered_at
        FROM event_registrations
        INNER JOIN users
            ON event_registrations.user_id = users.user_id
        INNER JOIN events
            ON event_registrations.event_id = events.event_id
        ORDER BY events.event_date ASC, event_registrations.registered_at DESC";

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

    <title>Event Registrations - NSBM EventHub</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .header {
            background: #17365d;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #17365d;
            color: white;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #17365d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .empty {
            text-align: center;
            padding: 30px;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>NSBM EventHub</h1>

    <p>Event Registrations</p>

</div>

<div class="container">

    <div class="table-box">

        <?php if ($result->num_rows > 0): ?>

            <table>

                <tr>

                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Event</th>
                    <th>Event Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Registered At</th>

                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $row["registration_id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["name"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["email"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["title"]); ?>
                        </td>

                        <td>
                            <?php
                            echo date(
                                "d F Y",
                                strtotime($row["event_date"])
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo date(
                                "h:i A",
                                strtotime($row["event_time"])
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["venue"]); ?>
                        </td>

                        <td>
                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime($row["registered_at"])
                            );
                            ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        <?php else: ?>

            <div class="empty">

                <h3>No event registrations yet.</h3>

                <p>
                    Students who join events will appear here.
                </p>

            </div>

        <?php endif; ?>

    </div>

    <a class="back" href="dashboard.php">
        ← Back to Dashboard
    </a>

</div>

</body>

</html>
