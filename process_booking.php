
<?php
require_once 'db.php';
require_once 'functions.php';

// Require login
if (!isLoggedIn()) {
    setFlashMessage('error', 'Session expired. Please login again.');
    redirect('login.php');
}

// Check if data is POSTed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid access method.');
    redirect('index.php');
}

// --- Retrieve POST data (Assume data was validated in step 2, but re-check crucial parts) ---
$flight_id = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
$num_passengers = filter_input(INPUT_POST, 'num_passengers', FILTER_VALIDATE_INT);
$total_price = filter_input(INPUT_POST, 'total_price', FILTER_VALIDATE_FLOAT);
$passenger_details_input = $_POST['passengers'] ?? [];
$user_id = $_SESSION['user_id']; // Get logged-in user ID

// --- Final Validation ---
if (!$flight_id || !$num_passengers || $num_passengers <= 0 || !$total_price || $total_price <= 0 || count($passenger_details_input) != $num_passengers) {
     setFlashMessage('error', 'Booking data missing or invalid.');
     // Redirect somewhere appropriate, maybe index or back to step 1 if possible
     redirect('index.php');
}

// --- Database Transaction (IMPORTANT for Atomicity) ---
$pdo->beginTransaction();

try {
    // 1. Re-check flight availability and lock the row
    $stmt = $pdo->prepare("SELECT seats_available FROM flights WHERE id = :id FOR UPDATE");
    $stmt->bindParam(':id', $flight_id, PDO::PARAM_INT);
    $stmt->execute();
    $flight = $stmt->fetch();

    if (!$flight) {
        throw new Exception("Flight not found.");
    }
    if ($flight['seats_available'] < $num_passengers) {
        throw new Exception("Sorry, not enough seats remaining ({$flight['seats_available']} left).");
    }

    // 2. Decrease seat count
    $new_seat_count = $flight['seats_available'] - $num_passengers;
    $stmt = $pdo->prepare("UPDATE flights SET seats_available = :count WHERE id = :id");
    $stmt->bindParam(':count', $new_seat_count, PDO::PARAM_INT);
    $stmt->bindParam(':id', $flight_id, PDO::PARAM_INT);
    $stmt->execute();

    // 3. Create Booking Record
    $booking_ref = generateSimpleBookingRef(); // Generate ref
    // ** Note: Add uniqueness check loop for $booking_ref in real app **

    $stmt = $pdo->prepare("INSERT INTO bookings (booking_ref, user_id, flight_id, num_passengers, total_price, status)
                           VALUES (:ref, :user, :flight, :pax, :price, 'Confirmed')");
    $stmt->bindParam(':ref', $booking_ref);
    $stmt->bindParam(':user', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':flight', $flight_id, PDO::PARAM_INT);
    $stmt->bindParam(':pax', $num_passengers, PDO::PARAM_INT);
    $stmt->bindParam(':price', $total_price);
    $stmt->execute();
    $booking_id = $pdo->lastInsertId();

    // 4. Insert Passenger Records
    $stmt = $pdo->prepare("INSERT INTO passengers (booking_id, full_name, dob) VALUES (:booking_id, :name, :dob)");
    foreach ($passenger_details_input as $pax) {
        $name = trim($pax['full_name'] ?? '');
        $dob = !empty($pax['dob']) ? trim($pax['dob']) : null; // Get DOB or null

        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':dob', $dob); // Bind DOB (can be null)
        $stmt->execute();
    }

    // 5. Commit Transaction
    $pdo->commit();

    // --- Booking Successful ---
    // Redirect to success page, passing the booking reference
    redirect('booking_success.php?ref=' . urlencode($booking_ref));

} catch (Exception $e) {
    // --- Booking Failed ---
    $pdo->rollBack(); // Roll back changes on any error
    setFlashMessage('error', 'Booking failed: ' . $e->getMessage());
    // Redirect back to step 1 or search results (consider error context)
    redirect('booking_step1.php?flight_id='.$flight_id.'&pax='.$num_passengers); // Redirect back
}
?>