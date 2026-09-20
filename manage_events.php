<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/database.php";

$message = "";

$csrf_token = $_SESSION["csrf_token"] ??= bin2hex(random_bytes(32));

/* Delete Event */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"], $_POST["delete"]) ||
        !hash_equals($csrf_token, $_POST["csrf_token"]) ||
        !is_numeric($_POST["delete"])
    ) {
        http_response_code(400);
        die("Invalid delete request.");
    }

    $event_id = intval($_POST["delete"]);

    $sql = "DELETE FROM events WHERE event_id = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        $message = "Event deleted successfully.";
    } else {
        $message = "Failed to delete event.";
    }

    $stmt->close();
}

/* Get All Events */
$sql = "SELECT event_id, title, description, event_date, event_time, venue
        FROM events
        ORDER BY event_date ASC, event_time ASC";

$result = $conn->query($sql);

if ($result === false) {
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <link rel="icon" type="image/png" href="favicon.png">

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Events - NSBM EventHub</title>

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

        .message {
            background: #dff0d8;
            color: #2d6a2d;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
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

        .edit {
            background: #f0ad4e;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .delete {
            background: #d9534f;
            color: white;
            padding: 7px 12px;
            border-radius: 4px;
            border: 0;
            cursor: pointer;
            font: inherit;
        }

        .delete-form {
            display: inline;
        }

        .add {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            background: #17365d;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>NSBM EventHub</h1>

    <p>Manage Events</p>

</div>

<div class="container">

    <?php if (!empty($message)): ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <a class="add" href="add_event.php">
        + Add New Event
    </a>

    <div class="table-box">

        <?php if ($result->num_rows > 0): ?>

            <table>

                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Actions</th>
                </tr>

                <?php while ($event = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $event["event_id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($event["title"]); ?>
                        </td>

                        <td>
                            <?php
                            echo date(
                                "d F Y",
                                strtotime($event["event_date"])
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo date(
                                "h:i A",
                                strtotime($event["event_time"])
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($event["venue"]); ?>
                        </td>

                        <td>

                            <a
                                class="edit"
                                href="edit_event.php?id=<?php echo $event["event_id"]; ?>"
                            >
                                Edit
                            </a>

                            <form
                                class="delete-form"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this event?');"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo htmlspecialchars($csrf_token); ?>"
                                >
                                <button
                                    class="delete"
                                    type="submit"
                                    name="delete"
                                    value="<?php echo $event["event_id"]; ?>"
                                >
                                    Delete
                                </button>
                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            </table>

        <?php else: ?>

            <p>No events available.</p>

        <?php endif; ?>

    </div>

    <a class="back" href="dashboard.php">
        ← Back to Dashboard
    </a>

</div>

</body>

</html>
