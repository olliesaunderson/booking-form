<?php

// Separate handling file to connect to the database with my
// credentials (no need to add on every page)

$host = 'dragon.ukc.ac.uk';
$dbname = 'octs2';
$user = 'octs2';
$password = '****';

$conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

