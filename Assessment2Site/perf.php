<?php

// Include the session handling file (/utils/session.php)
require_once __DIR__ . "/utils/session.php";

// Include the database handling file (/utils/db.php)
require_once __DIR__ . "/utils/db.php";

// Adds 'userFullName' and 'userEmail' to the session (these match the inputs from the form in index.html)
$_SESSION['userFullName'] = $_GET['userFullName'];
$_SESSION['userEmail'] = $_GET['userEmail'];

// mySQL select query to get all performances from the database
try {
    $sql = "SELECT * FROM Performance p JOIN Production r ON p.Title = r.Title";
    $handle = $conn->prepare($sql);
    $handle->execute();
    $conn = null;
    $res = $handle->fetchAll();
} catch (PDOException $exception) {
    echo $exception->getMessage();
    die;
}

?>

<!-- HTML code for the page title, stylesheet and information to direct the customer -->
<html lang="en">

<head>
    <title>Performances</title>
    <link rel="stylesheet" href="mystyles.css">
</head>

<body>

<h1>The University of Kent Theatre</h1>

<!-- Get the name that the customer input in the previous page from the session and
combined with HTML to welcome them personally -->
<h2> Welcome, <?php echo $_GET['userFullName'] ?>!</h2>

<p> Select a performance from the list below to show the available seats.</p>

<!-- Sets up the table in which the performance details will go, each heading defined -->
<table>
    <tr>
        <th>Title</th>
        <th>Date</th>
        <th>Time</th>
        <th>Availability</th>
    </tr>

<!-- For each response from the server (each production from the mySQL select query), a table row is created
 and four cells are defined; 'Title', 'PerfDate', PerfTime' and a form linking to 'seats.php'.
 The form takes 5 inputs, 4 hidden (each taken from the mySQL query) and a visible submit button -->
    <?php
    foreach ($res as $row) {
        echo "<tr>";
        echo "<td>" . $row['Title'] . "</td>";
        echo "<td>" . $row['PerfDate'] . "</td>";
        echo "<td>" . $row['PerfTime'] . "</td>";
        echo "<td>";
        echo "<form action='seats.php' method='post'>";
        echo "<input type='hidden' name='Title' value='" . $row['Title'] . "' />";
        echo "<input type='hidden' name='PerfDate' value='" . $row['PerfDate'] . "' />";
        echo "<input type='hidden' name='PerfTime' value='" . $row['PerfTime'] . "' />";
        echo "<input type='hidden' name='BasicTicketPrice' value='" . $row['BasicTicketPrice'] . "' />";
        echo "<input type='submit' class='perfButton' value='Show Availability' />";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    ?>

</table>

</body>

</html>
