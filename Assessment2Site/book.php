<?php

// Include the session handling file (/utils/session.php)
require_once __DIR__ . "/utils/session.php";

// Include the database handling file (/utils/db.php)
require_once __DIR__ . "/utils/db.php";

// Adds data to the session, if empty then it will be set to null
$title = $_SESSION['performanceTitle'] ?? null;
$userEmail = $_SESSION['userEmail'] ?? null;
$performanceDate = $_SESSION['performanceDate'] ?? null;
$performanceTime = $_SESSION['performanceTime'] ?? null;
$seats = $_POST ?? null;

/// Security check (make sure the user has come from 'seats.php' and not directly to 'book.php')
// If each of the parameters is empty then the customer will be redirected back to 'index.html'
if (empty($userEmail) || empty($performanceDate) || empty($performanceTime) || empty($seats) || empty($title)) {
    header('Location: index.html');
    exit;
}

// The mySQL query for inserting the customers data into the database
$sql = "INSERT INTO Booking VALUES (?, ?, ?, ?)";
$handle = $conn->prepare($sql);

// Defines which data is to be added into the database
// Begins a transaction, adds the data via the mySQL query, if an exception is thrown
// during the transaction then it will rollback and return an exception message.
try {
    $conn->beginTransaction();
    foreach ($seats as $seatNumber => $seatPrice) {
        $data = [$userEmail, $performanceDate, $performanceTime, $seatNumber];
        $handle->execute($data);
    }
    $conn->commit();
} catch (PDOException $exception) {
    $conn->rollBack();
    echo $exception->getMessage();
    die;
}

?>

<!-- HTML for the page title (specifies the selected performance title by referencing the  $title' declared above)
, stylesheet and information to direct the customer. In the h2 tag, 'userFullName' is also used (already
added to the session) and in the p tags '$title', '$performanceDate' and '$performanceTime' are all added
the same way as '$title' in the title tag -->
<html lang="en">

<head>
    <title>Seats for <?php echo $title ?></title>
    <link rel="stylesheet" href="mystyles.css">
</head>

<body>

<h1>The University of Kent Theatre</h1>

<h2> <?php echo $_SESSION['userFullName'] ?>, thank you for your purchase! </h2>

<p>Below is a summary of your booking for <?php echo $title ?> On <?php echo $performanceDate ?> At <?php echo $performanceTime ?> </p>

<!-- For each seat booked by the customer, the '$seatNumber' is output in a list-->
<ul>
    <?php
    foreach ($seats as $seatNumber => $seatPrice) {
        echo "<li>Seat " . $seatNumber . " booked succesfully.</li>";
    }
    ?>
</ul>

</body>
</html>