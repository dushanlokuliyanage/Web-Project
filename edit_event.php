<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/database.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid event ID.");
}

$event_id = intval($_GET["id"]);
$message = "";
$message_type = "";


/* =========================
   UPDATE EVENT
========================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];
    $event_time = $_POST["event_time"];
    $venue = trim($_POST["venue"]);
    $category_id = intval($_POST["category_id"]);
    $capacity = intval($_POST["capacity"]);


    /* Get current image */

    $image_sql = "SELECT image FROM events WHERE event_id = ?";

    $image_stmt = $conn->prepare($image_sql);
    $image_stmt->bind_param("i", $event_id);
    $image_stmt->execute();

    $image_result = $image_stmt->get_result();

    if ($image_result->num_rows == 0) {
        die("Event not found.");
    }

    $current_event = $image_result->fetch_assoc();

    $current_image = $current_event["image"];

    $image_stmt->close();

    $new_image = $current_image;


    /* =========================
       VALIDATION
    ========================= */

    if (
        empty($title) ||
        empty($description) ||
        empty($event_date) ||
        empty($event_time) ||
        empty($venue) ||
        $category_id <= 0 ||
        $capacity <= 0
    ) {

        $message = "Please fill all fields correctly.";
        $message_type = "error";

    } else {


        /* =========================
           NEW IMAGE UPLOAD
        ========================= */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] == 0
        ) {

            $file_name = $_FILES["image"]["name"];
            $file_tmp = $_FILES["image"]["tmp_name"];
            $file_size = $_FILES["image"]["size"];

            $file_ext = strtolower(
                pathinfo(
                    $file_name,
                    PATHINFO_EXTENSION
                )
            );

            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "gif",
                "webp"
            ];


            if (!in_array($file_ext, $allowed_extensions)) {

                $message =
                    "Only JPG, JPEG, PNG, GIF and WEBP images are allowed.";

                $message_type = "error";

            } elseif ($file_size > 5 * 1024 * 1024) {

                $message =
                    "Image size must be less than 5MB.";

                $message_type = "error";

            } elseif (@getimagesize($file_tmp) === false) {

                $message =
                    "The uploaded file is not a valid image.";

                $message_type = "error";

            } else {

                $new_image =
                    time() . "_" .
                    uniqid() . "." .
                    $file_ext;

                $upload_folder = __DIR__ . "/image/";

                if (!is_dir($upload_folder)) {

                    mkdir(
                        $upload_folder,
                        0755,
                        true
                    );
                }

                $upload_path =
                    $upload_folder . $new_image;


                if (
                    move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {

                    /* Delete old image */

                    if (
                        !empty($current_image) &&
                        file_exists(
                            $upload_folder . $current_image
                        )
                    ) {

                        unlink(
                            $upload_folder . $current_image
                        );
                    }

                } else {

                    $message =
                        "Failed to upload new image.";

                    $message_type = "error";

                    $new_image = $current_image;
                }
            }
        }


        /* =========================
           UPDATE DATABASE
        ========================= */

        if (empty($message)) {

            $sql = "UPDATE events
                    SET
                        title = ?,
                        description = ?,
                        event_date = ?,
                        event_time = ?,
                        venue = ?,
                        capacity = ?,
                        category_id = ?,
                        image = ?
                    WHERE event_id = ?";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {

                die(
                    "Database error: " .
                    $conn->error
                );
            }


            $stmt->bind_param(
                "sssssissi",
                $title,
                $description,
                $event_date,
                $event_time,
                $venue,
                $capacity,
                $category_id,
                $new_image,
                $event_id
            );


            if ($stmt->execute()) {

                $message =
                    "Event updated successfully! 🎉";

                $message_type = "success";

            } else {

                $message =
                    "Failed to update event: " .
                    $stmt->error;

                $message_type = "error";
            }

            $stmt->close();
        }
    }
}


/* =========================
   GET EVENT
========================= */

$sql = "SELECT
            event_id,
            title,
            description,
            event_date,
            event_time,
            venue,
            capacity,
            category_id,
            image
        FROM events
        WHERE event_id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {

    die(
        "Database error: " .
        $conn->error
    );
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
    Edit Event - NSBM EventHub
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

.header {

    background: #17365d;

    color: white;

    padding: 20px;

    text-align: center;
}

.header h1 {

    margin: 0;
}

.container {

    width: 90%;

    max-width: 700px;

    margin: 40px auto;

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.1);
}

h2 {

    color: #17365d;

    text-align: center;
}

label {

    display: block;

    margin-top: 15px;

    margin-bottom: 6px;

    font-weight: bold;
}

input,
textarea {

    width: 100%;

    padding: 11px;

    border: 1px solid #ccc;

    border-radius: 5px;

    font-size: 15px;
}

textarea {

    height: 120px;

    resize: vertical;
}

.current-image {

    width: 100%;

    max-height: 250px;

    object-fit: cover;

    border-radius: 8px;

    margin-top: 10px;
}

input[type="file"] {

    padding: 8px;

    background: #f9f9f9;
}

.help {

    font-size: 13px;

    color: #777;
}

button {

    width: 100%;

    margin-top: 25px;

    padding: 13px;

    background: #17365d;

    color: white;

    border: none;

    border-radius: 5px;

    cursor: pointer;

    font-size: 16px;

    font-weight: bold;
}

button:hover {

    background: #0f2745;
}

.message {

    padding: 12px;

    margin-bottom: 20px;

    border-radius: 5px;

    text-align: center;

    font-weight: bold;
}

.success {

    background: #d4edda;

    color: #155724;
}

.error {

    background: #f8d7da;

    color: #721c24;
}

.back {

    display: inline-block;

    margin-top: 20px;

    padding: 10px 16px;

    background: #777;

    color: white;

    text-decoration: none;

    border-radius: 5px;
}

</style>

</head>


<body>


<div class="header">

    <h1>
        NSBM EventHub
    </h1>

    <p>
        Edit Event
    </p>

</div>


<div class="container">

<h2>
    ✏️ Update Event
</h2>


<?php if (!empty($message)): ?>

<div class="message <?php echo $message_type; ?>">

    <?php
    echo htmlspecialchars($message);
    ?>

</div>

<?php endif; ?>


<form
    method="POST"
    enctype="multipart/form-data"
>


<label>
    Event Title
</label>

<input
    type="text"
    name="title"
    value="<?php echo htmlspecialchars($event["title"]); ?>"
    required
>


<label>
    Description
</label>

<textarea
    name="description"
    required
><?php echo htmlspecialchars($event["description"]); ?></textarea>


<label>
    Event Date
</label>

<input
    type="date"
    name="event_date"
    value="<?php echo htmlspecialchars($event["event_date"]); ?>"
    required
>


<label>
    Event Time
</label>

<input
    type="time"
    name="event_time"
    value="<?php echo htmlspecialchars($event["event_time"]); ?>"
    required
>


<label>
    Venue
</label>

<input
    type="text"
    name="venue"
    value="<?php echo htmlspecialchars($event["venue"]); ?>"
    required
>


<label>
    Event Capacity
</label>

<input
    type="number"
    name="capacity"
    min="1"
    value="<?php echo htmlspecialchars($event["capacity"]); ?>"
    required
>

<p class="help">
    Maximum number of students who can join.
</p>


<label>
    Category ID
</label>

<input
    type="number"
    name="category_id"
    min="1"
    value="<?php echo htmlspecialchars($event["category_id"]); ?>"
    required
>


<label>
    Current Event Image
</label>

<?php if (!empty($event["image"])): ?>

    <img
        class="current-image"
        src="image/<?php echo htmlspecialchars($event["image"]); ?>"
        alt="Current Event Image"
    >

<?php else: ?>

    <p class="help">
        No image uploaded.
    </p>

<?php endif; ?>


<label>
    Change Event Image
</label>

<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.gif,.webp"
>

<p class="help">
    JPG, JPEG, PNG, GIF or WEBP. Maximum 5MB.
</p>


<button type="submit">
    💾 Update Event
</button>


</form>


<a
    class="back"
    href="manage_events.php"
>
    ← Back to Manage Events
</a>


</div>

</body>

</html>
```
