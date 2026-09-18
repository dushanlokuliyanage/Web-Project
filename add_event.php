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


$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];
    $event_time = $_POST["event_time"];
    $venue = trim($_POST["venue"]);
    $category_id = intval($_POST["category_id"]);
    $capacity = intval($_POST["capacity"]);

    $image_name = NULL;


    /* =========================
       CHECK REQUIRED FIELDS
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
           IMAGE UPLOAD
        ========================= */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] == 0
        ) {

            $file_name =
                $_FILES["image"]["name"];

            $file_tmp =
                $_FILES["image"]["tmp_name"];

            $file_size =
                $_FILES["image"]["size"];


            $file_ext =
                strtolower(
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


            if (
                !in_array(
                    $file_ext,
                    $allowed_extensions
                )
            ) {

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

                /*
                 * Create a unique filename
                 */

                $image_name =
                    time() . "_" .
                    uniqid() . "." .
                    $file_ext;


                $upload_folder =
                    __DIR__ . "/image/";


                /*
                 * Create folder if it doesn't exist
                 */

                if (
                    !is_dir(
                        $upload_folder
                    )
                ) {

                    mkdir(
                        $upload_folder,
                        0755,
                        true
                    );
                }


                $upload_path =
                    $upload_folder .
                    $image_name;


                if (
                    !move_uploaded_file(
                        $file_tmp,
                        $upload_path
                    )
                ) {

                    $message =
                        "Failed to upload image.";

                    $message_type = "error";

                    $image_name = NULL;
                }
            }
        }


        /* =========================
           INSERT EVENT
        ========================= */

        if (
            empty($message)
        ) {

            $sql = "INSERT INTO events
                    (
                        title,
                        description,
                        event_date,
                        event_time,
                        venue,
                        capacity,
                        category_id,
                        image
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


            $stmt =
                $conn->prepare($sql);


            if ($stmt === false) {

                die(
                    "Database error: " .
                    $conn->error
                );
            }


            $stmt->bind_param(
                "sssssiss",
                $title,
                $description,
                $event_date,
                $event_time,
                $venue,
                $capacity,
                $category_id,
                $image_name
            );


            if ($stmt->execute()) {

                $message =
                    "Event added successfully! 🎉";

                $message_type = "success";

            } else {

                $message =
                    "Failed to add event: " .
                    $stmt->error;

                $message_type = "error";
            }


            $stmt->close();
        }
    }
}
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
    Add Event - NSBM EventHub
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

    padding: 20px;

    text-align: center;
}

.header h1 {

    margin: 0;
}


/* Container */

.container {

    max-width: 700px;

    margin: 40px auto;

    background: white;

    padding: 30px;

    border-radius: 10px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.1);
}


/* Heading */

h2 {

    text-align: center;

    color: #17365d;
}


/* Labels */

label {

    display: block;

    margin-top: 16px;

    margin-bottom: 6px;

    font-weight: bold;
}


/* Inputs */

input,
textarea,
select {

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


/* File input */

input[type="file"] {

    padding: 8px;

    background: #f9f9f9;
}


/* Help text */

.help {

    font-size: 13px;

    color: #777;

    margin-top: 5px;
}


/* Button */

button {

    margin-top: 25px;

    width: 100%;

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


/* Messages */

.message {

    text-align: center;

    margin-bottom: 20px;

    padding: 12px;

    border-radius: 5px;

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


/* Back */

.back {

    display: inline-block;

    margin-top: 20px;

    padding: 10px 16px;

    background: #777;

    color: white;

    text-decoration: none;

    border-radius: 5px;
}


/* Mobile */

@media (max-width: 600px) {

    .container {

        width: 95%;

        padding: 20px;
    }

}

</style>
<script src="../js/script.js"></script>

</head>


<body>


<div class="header">

    <h1>
        NSBM EventHub
    </h1>

    <p>
        Add New Event
    </p>

</div>


<div class="container">


<h2>
    Create Event
</h2>


<?php if (!empty($message)): ?>

    <div
        class="message
        <?php echo $message_type; ?>"
    >

        <?php
        echo htmlspecialchars(
            $message
        );
        ?>

    </div>

<?php endif; ?>


<form
    method="POST"
    enctype="multipart/form-data"
    id="eventForm"
>


<!-- Title -->

<label>
    Event Title
</label>

<input
    type="text"
    name="title"
    placeholder="Enter event title"
    required
>


<!-- Description -->

<label>
    Description
</label>

<textarea
    name="description"
    placeholder="Enter event description"
    required
></textarea>


<!-- Date -->

<label>
    Event Date
</label>

<input
    type="date"
    name="event_date"
    required
>


<!-- Time -->

<label>
    Event Time
</label>

<input
    type="time"
    name="event_time"
    required
>


<!-- Venue -->

<label>
    Venue
</label>

<input
    type="text"
    name="venue"
    placeholder="Enter event venue"
    required
>


<!-- Capacity -->

<label>
    Event Capacity
</label>

<input
    type="number"
    name="capacity"
    id="capacity"
    min="1"
    placeholder="Example: 100"
    required
>

<p class="help">
    Enter the maximum number of students
    who can join this event.
</p>


<!-- Category -->

<label>
    Category ID
</label>

<input
    type="number"
    name="category_id"
    min="1"
    placeholder="Example: 4"
    required
>

<p class="help">
    Enter the category ID from the categories table.
</p>


<!-- Image -->

<label>
    Event Image
</label>

<input
    type="file"
    name="image"
    id="eventImage"
    accept=".jpg,.jpeg,.png,.gif,.webp"
>
<img
    id="imagePreview"
    src=""
    alt="Image Preview"
    style="
        display: none;
        width: 100%;
        max-height: 250px;
        object-fit: cover;
        margin-top: 10px;
        border-radius: 8px;
    "
>

<p class="help">
    JPG, JPEG, PNG, GIF or WEBP.
    Maximum size: 5MB.
</p>


<!-- Submit -->

<button type="submit">

    🎉 Add Event

</button>


</form>


<a
    class="back"
    href="dashboard.php"
>
    ← Back to Dashboard
</a>


</div>


</body>

</html>
