<?php
$host = 'localhost';
$dbname = 'airline_exam_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo; // Correct return of the PDO object
} catch (PDOException $e) {
    // Handle the error (e.g., log it or display a message)
    error_log("Database connection failed: " . $e->getMessage());
    return null; // Return null or false to indicate failure
}
?>