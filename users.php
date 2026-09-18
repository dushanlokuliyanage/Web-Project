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

$sql = "SELECT
            user_id,
            name,
            email,
            role,
            created_at
        FROM users
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result === false) {
    die("Database error: " . $conn->error);
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
    User Management - NSBM EventHub
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
    padding: 25px;
    text-align: center;
}

.header h1 {
    margin: 0;
}

.container {
    width: 95%;
    max-width: 1100px;
    margin: 35px auto;
}

.table-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    overflow-x: auto;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 13px;
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

.role {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: bold;
}

.admin {
    background: #ffe0e0;
    color: #c62828;
}

.student {
    background: #e0f2f1;
    color: #00695c;
}

.back {
    display: inline-block;
    margin-top: 20px;
    padding: 11px 18px;
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

    <h1>
        NSBM EventHub
    </h1>

    <p>
        User Management
    </p>

</div>


<div class="container">


<div class="table-box">


<?php if ($result->num_rows > 0): ?>


<table>

<thead>

<tr>

    <th>ID</th>

    <th>Name</th>

    <th>Email</th>

    <th>Role</th>

    <th>Registered Date</th>

</tr>

</thead>


<tbody>


<?php while ($user = $result->fetch_assoc()): ?>


<tr>

    <td>
        <?php
        echo $user["user_id"];
        ?>
    </td>


    <td>
        <?php
        echo htmlspecialchars(
            $user["name"]
        );
        ?>
    </td>


    <td>
        <?php
        echo htmlspecialchars(
            $user["email"]
        );
        ?>
    </td>


    <td>

        <?php if ($user["role"] == "admin"): ?>

            <span class="role admin">
                Admin
            </span>

        <?php else: ?>

            <span class="role student">
                Student
            </span>

        <?php endif; ?>

    </td>


    <td>

        <?php
        echo date(
            "d M Y, h:i A",
            strtotime(
                $user["created_at"]
            )
        );
        ?>

    </td>

</tr>


<?php endwhile; ?>


</tbody>

</table>


<?php else: ?>


<div class="empty">

    <h3>
        No users found.
    </h3>

</div>


<?php endif; ?>


</div>


<a
    class="back"
    href="dashboard.php"
>
    ← Back to Dashboard
</a>


</div>


</body>

</html>
