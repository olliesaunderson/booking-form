<?php

// Include the session handling file (/utils/session.php)
require_once __DIR__ . "/utils/session.php";

// Include the database handling file (/utils/db.php)
require_once __DIR__ . "/utils/db.php";

// Get the performance details from the request (_POST)
$title = $_POST['Title'] ?? null;
$performanceDate = $_POST['PerfDate'] ?? null;
$performanceTime = $_POST['PerfTime'] ?? null;
$basicTicketPrice = $_POST['BasicTicketPrice'] ?? null;

// Security check (make sure the user has come from 'perf.php' and not directly to 'seats.php')
// If each of the parameters is empty then the customer will be redirected back to 'index.html'
if (empty($title) || empty($performanceDate) || empty($performanceTime) || empty($basicTicketPrice)) {
    header('Location: index.html');
    exit;
}

// Adds the performance data to session
$_SESSION['performanceTitle'] = $title;
$_SESSION['performanceDate'] = $performanceDate;
$_SESSION['performanceTime'] = $performanceTime;

// mySQL select query to get all seats for the selected performance from the database
try {
    $sql = "SELECT Seat.RowNumber, ROUND(Zone.PriceMultiplier * :basicTicketPrice, 2) AS Price FROM Seat JOIN Zone ON Zone.Name=Seat.Zone
                WHERE Seat.RowNumber NOT IN
                (SELECT Booking.RowNumber FROM Booking WHERE Booking.PerfTime = :perfTime
                AND Booking.PerfDate = :perfDate)";

    $handle = $conn->prepare($sql);
    $handle->bindValue(":basicTicketPrice", $basicTicketPrice);
    $handle->bindValue(":perfTime", $performanceTime);
    $handle->bindValue(":perfDate", $performanceDate);
    $handle->execute();
    $conn = null;
    $res = $handle->fetchAll();
} catch (PDOException $exception) {
    echo $exception->getMessage();
    die;
}

?>

<!-- HTML code for the page title (specifies the selected performance title by referencing the $_POST)
, stylesheet and information to direct the customer. In the h2 tag, 'userFullName' is also used (already
added to the session) and in the h3 tags '$title', '$performanceDate' and '$performanceTime' are all added
the same way as '$title' in the title tag -->
<html lang="en">

<head>
    <title>Seats for <?php echo $title ?></title>
    <link rel="stylesheet" href="mystyles.css">
</head>

<body>

<h1>The University of Kent Theatre</h1>

<h2> <?php echo $_SESSION['userFullName'] ?>, select your seat/s from the table below. </h2>

<p>Once you are happy with your selection, press book to confirm your booking.</p>

<form action="book.php" method="post" class="seatsForm">

    <p>Total: </p> <p id="priceSummary"></p>
    <p>Seats: </p> <p id="seatsSummary"></p>
    <input id="bookButton" type="submit" value="Book">

    <h3>Showing Available Seats For <?php echo $title ?> On <?php echo $performanceDate ?> At <?php echo $performanceTime ?></h3>

<!-- Sets up the table in which the seat details will go, each heading defined -->
    <table class="seatsTable">
        <tr>
            <th>Seat</th>
            <th>Price</th>
            <th>Select</th>
        </tr>

<!-- For each response from the server (each seat from the mySQL select query), a table row is created
 and three cells are defined; 'RowNumber', 'Price' and a checkbox input.
 The checkbox name matches the 'RowNumber' and the value matches the 'Price', it then has an onchange
 function which links to the JS below and allows the customer to see a live summary of the seats they
 are booking and the total price. -->
        <?php
        foreach ($res as $row) {
            echo "<tr>";
            echo "<td class='seatsCells'>" . $row['RowNumber'] . "</td>";
            echo "<td class='seatsCells'>" . $row['Price'] . "</td>";
            echo "<td class='seatsCells'>";
            echo "<input type='checkbox' class='checkbox' name='" . $row['RowNumber'] . "' value='" . $row['Price'] . "' onchange='selectedSeats()'/>";
            echo "</td>";
            echo "</tr>";
        }

        ?>

    </table>



</form>

<!--The JS creates a function called 'selectedSeats' and then defines 4 variables; 'checkBoxes' which
is the checkbox in the table, 'totalPrice' which is a float set to 0.00, 'bookedSeat' which is an empty
string and num which is an integer set to 0 -->
<script>
    function selectedSeats() {
        var checkBoxes = document.getElementsByClassName('checkbox');
        var totalPrice = 0.00;
        var bookedSeat = ' ';
        var num = 0;

// A for loop which runs as long as it is smaller than the length of checkBoxes (checks every seat's checkbox)
        for (var i = 0; i < checkBoxes.length; i++){

// If the checkbox is checked;
            if(checkBoxes[i].checked){
// The name of that checkbox (RowNumber) is added to 'bookedSeat' with a ',' to separate the seats, the
// element with the ID 'seatSummary is set to block so it is visible and 'bookedSeat' is added within it.
                bookedSeat += checkBoxes[i].name + ', ';
                document.getElementById('seatsSummary').style.display = 'block';
                document.getElementById('seatsSummary').innerHTML = bookedSeat;

// The value of the selected checkbox is added to 'totalPrice' using parseFloat to ensure it is a floating point
// number, the element with ID 'priceSummary' is set to block so it is visible and 'totalPrice' is added within it.
                totalPrice = parseFloat(totalPrice) + parseFloat(checkBoxes[i].value);
                document.getElementById('priceSummary').style.display = 'block';
                document.getElementById('priceSummary').innerHTML = totalPrice;

// The book button is re enabled and will allow the customer to use it to make their booking now that there is
// at least one seat selected, 'num' is also increased by 1.
                document.getElementById('bookButton').disabled = false;
                num += 1;
            }
// If the checkbox isn't checked then totalPrice remains at 0.00 and the price and seat summary remain undisplayed.
            else if (totalPrice === 0.00){
                document.getElementById('seatsSummary').style.display = 'none';
                document.getElementById('priceSummary').style.display = 'none';
            }
// If num === 0 the book button remains disabled as this means a seat isn't selected.
            if (num === 0){
                document.getElementById('bookButton').disabled = true;
            }
         }
     }
</script>


</body>

</html>
