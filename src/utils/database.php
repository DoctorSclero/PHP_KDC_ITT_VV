<?php 

// Connessione al database.
$hostname = "db";
$username = "root";
$password = "root";
$database = "itt_kdc";

// Instanzio la connesione al database.
$connection = new mysqli(
    $hostname,
    $username,
    $password,
    $database
);


?>