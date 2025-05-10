
<?php
require_once 'db.php';
require_once 'functions.php';

// Require login
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to continue.');
    redirect('login.php');
}

// Check if data is POSTed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid access method.');
    redirect('index.php');
}

// Get data from POST (basic sanitization)
$flight_id = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
$num_passengers = filter_input(INPUT_POST, 'num_passengers', FILTER_VALIDATE_INT);
$total_price = filter_input(INPUT_POST, 'total_price', FILTER_VALIDATE_FLOAT);
$passenger_details_input = $_POST['passengers'] ?? []; // Array of passenger data

// --- Validation ---
$errors = [];
if (!$flight_id) $errors[] = "Invalid Flight ID.";
if (!$num_passengers || $num_passengers <= 0) $errors[] = "Invalid number of passengers.";
if (!$total_price || $total_price <= 0) $errors[] = "Invalid price.";
if (count($passenger_details_input) != $num_passengers) {
     $errors[] = "Passenger details count mismatch.";
}

// Validate each passenger's name
$passenger_details = [];
foreach ($passenger_details_input as $index => $pax) {
    $name = trim($pax['full_name'] ?? '');
    $dob = trim($pax['dob'] ?? '');
    if (empty($name)) {
        $errors[] = "Full Name is required for Passenger {$index}.";
    }
     // Basic DOB format check if provided
    if (!empty($dob) && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dob)) {
        $errors[] = "Invalid Date of Birth format for Passenger {$index}.";
        $dob = null; // Clear invalid DOB
    } elseif (empty($dob)) {
        $dob = null; // Ensure empty DOB is stored as NULL
    }

    // Store sanitized data
    $passenger_details[$index] = [
        'full_name' => sanitize($name), // Sanitize before storing/displaying
        'dob' => $dob // Store validated DOB or null
    ];
}


// Fetch flight details again for confirmation display
$flight = null;
if (!$errors) {
    try {
        $sql = "SELECT f.*, a.name as airline_name, orig.city as origin_city, dest.city as destination_city
                FROM flights f
                JOIN airlines a ON f.airline_code = a.code
                JOIN airports orig ON f.origin_code = orig.code
                JOIN airports dest ON f.destination_code = dest.code
                WHERE f.id = :id"; // No need to check seats here, will be checked finally in process_booking
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $flight_id, PDO::PARAM_INT);
        $stmt->execute();
        $flight = $stmt->fetch();

        if (!$flight) {
            $errors[] = 'Flight details could not be retrieved.';
        }
    } catch (PDOException $e) {
         $errors[] = 'Database error fetching flight details: ' . $e->getMessage();
    }
}

if (!empty($errors)) {
     // If errors, store them in session and redirect back to step 1
     $_SESSION['booking_errors'] = $errors; // Store errors
     $_SESSION['form_data'] = $_POST; // Optionally store form data to re-fill
     setFlashMessage('error', 'Please correct the errors below.');
     // Redirect back to booking step 1, passing flight id and pax again
     redirect('booking_step1.php?flight_id='.$flight_id.'&pax='.$num_passengers);
}

$departure_dt = new DateTime($flight['departure_time']);
$arrival_dt = new DateTime($flight['arrival_time']);

$pageTitle = "Confirm Booking";
require_once 'header.php';
?>
<style>
    :root {
        /* Modern color palette - Same as register.php */
        --primary-color: #0ea5e9; /* Sky-500 */
        --primary-dark: #0284c7;  /* Sky-600 */
        --primary-light: #38bdf8; /* Sky-400 */
        --accent-color: #14b8a6;  /* Teal-500 */
        --highlight: #fbbf24;     /* Amber-400 */
        --highlight-light: #fcd34d; /* Amber-300 */
    }

    body {
        background-color: #f8fafc;
        background-image: url('assets/images/sky-pattern.png');
        background-size: cover;
        background-attachment: fixed;
        background-position: center;
    }

    /* Enhanced card styling */
    .card {
        transition: all 0.4s ease;
        max-width: 900px;
        margin: 2rem auto;
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 0.5rem;
    }

    .card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(-5px);
    }
    
    /* Form control styling */
    .form-control {
        transition: all 0.3s ease;
        height: calc(3rem + 2px);
        border-radius: 0.5rem;
        font-size: 1rem;
        display: block;
        width: 100%;
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
    }

    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
        border-color: var(--primary-color) !important;
        transform: translateY(-2px);
    }
    
    /* Label styling */
    .form-label {
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
        font-size: 1rem;
        display: inline-block;
    }
    
    .form-label:hover {
        transform: translateX(3px);
    }
    
    /* Card header custom animation */
    @keyframes slideGradient {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }
    
    .card-header {
        background-size: 200% auto;
        animation: slideGradient 10s ease infinite;
        background-image: linear-gradient(135deg, var(--primary-color), var(--accent-color), var(--primary-dark));
        color: white;
        padding: 1.5rem;
        border-top-left-radius: 0.5rem !important;
        border-top-right-radius: 0.5rem !important;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Flight summary card special styling */
    .flight-summary {
        background: linear-gradient(to right, rgba(255, 255, 255, 0.9), rgba(240, 249, 255, 0.9));
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }
    
    .flight-summary:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.2), 0 4px 6px -2px rgba(14, 165, 233, 0.1);
        transform: scale(1.01);
    }
    
    /* Custom button styling */
    .proceed-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        padding: 0.75rem 2.5rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2), 0 2px 4px -1px rgba(14, 165, 233, 0.1);
        height: calc(3rem + 2px);
        cursor: pointer;
    }
    
    .proceed-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3), 0 4px 6px -2px rgba(14, 165, 233, 0.15);
        transform: translateY(-3px) scale(1.03);
        background: linear-gradient(135deg, var(--primary-dark), var(--accent-color));
        color: white;
    }
    
    .proceed-btn:active {
        transform: translateY(1px);
    }
    
    .back-btn {
        background: white;
        color: var(--primary-color);
        font-weight: 600;
        border: 2px solid var(--primary-color);
        border-radius: 50px;
        padding: 0.75rem 2rem;
        transition: all 0.3s ease;
        height: calc(3rem + 2px);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
    }
    
    .back-btn:hover {
        background-color: rgba(14, 165, 233, 0.1);
        transform: translateX(-3px);
    }
    
    /* Price highlight */
    .price-highlight {
        color: #059669; /* Emerald-600 */
        font-size: 1.25rem;
        font-weight: 700;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .price-highlight:hover {
        transform: scale(1.05);
        text-shadow: 0 0 5px rgba(5, 150, 105, 0.2);
    }
    
    /* Animation for elements */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate__animated {
        animation-duration: 1s;
        animation-fill-mode: both;
    }
    
    .animate__fadeIn {
        animation-name: fadeIn;
    }
    
    .animate__fadeInUp {
        animation-name: fadeInUp;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 20px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
    
    .animate__delay-1s {
        animation-delay: 0.2s;
    }
    
    .animate__delay-2s {
        animation-delay: 0.4s;
    }
    
    .animate__delay-3s {
        animation-delay: 0.6s;
    }
    
    /* Passenger card styling */
    .passenger-card {
        border-radius: 0.5rem;
        border: 1px solid rgba(14, 165, 233, 0.2);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .passenger-card:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.2), 0 4px 6px -2px rgba(14, 165, 233, 0.1);
        border-color: var(--primary-color);
    }
    
    .passenger-card .card-header {
        background: linear-gradient(135deg, var(--primary-light), var(--accent-color));
        color: white;
        font-weight: 600;
        font-size: 1.125rem;
        padding: 1rem 1.5rem;
    }
    
    .passenger-card .card-body {
        padding: 1.5rem;
        background-color: white;
    }
    
    /* Input group styling */
    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
        margin-bottom: 1.5rem;
    }
    
    .input-group-text {
        border-top-left-radius: 0.5rem;
        border-bottom-left-radius: 0.5rem;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        background-color: #fff;
        border: 1px solid #ced4da;
    }
    
    .input-group:focus-within {
        transform: scale(1.01);
        transition: transform 0.3s ease;
    }
    
    /* Alert styling */
    .alert {
        border-radius: 0.5rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid transparent;
    }
    
    .alert-info {
        background-color: rgba(224, 242, 254, 0.8);
        border-color: rgba(186, 230, 253, 0.5);
        color: #0369a1;
    }
    
    /* Form text help */
    .form-text {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        transition: all 0.3s ease;
    }
    
    input:focus + .form-text, 
    input:valid + .form-text {
        color: var(--primary-color);
    }
    
    /* Row and column layout fixes */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -0.75rem;
        margin-left: -0.75rem;
    }
    
    .g-3 > * {
        padding-right: 0.75rem;
        padding-left: 0.75rem;
    }
    
    .col-md-6 {
        position: relative;
        width: 100%;
        padding-right: 0.75rem;
        padding-left: 0.75rem;
    }
    
    @media (min-width: 768px) {
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
    
    /* Container for page structure */
    .container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
        max-width: 1140px;
    }
    
    /* Additional utility classes */
    .d-flex {
        display: flex !important;
    }
    
    .justify-content-between {
        justify-content: space-between !important;
    }
    
    .text-danger {
        color: #ef4444 !important;
    }
    
    .font-semibold {
        font-weight: 600 !important;
    }
    
    .text-3xl {
        font-size: 1.875rem !important;
    }
    
    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
    
    .mt-4 {
        margin-top: 1.5rem !important;
    }
    
    .text-indigo-800 {
        color: #3730a3 !important;
    }
</style>

<h1 class="text-3xl font-bold mb-4 text-indigo-800">Confirm Your Booking</h1>
<p class="text-gray-600 mb-4">Please review the details below before confirming your booking.</p>

<div class="card shadow-sm border border-gray-200">
    <div class="card-header bg-gray-100 font-semibold">Booking Summary</div>
    <div class="card-body space-y-3">
        <div>
            <h5 class="text-lg font-semibold mb-2">Flight Details</h5>
            <p><strong>Flight:</strong> <?php echo sanitize($flight['airline_name']) . ' ' . sanitize($flight['flight_number']); ?></p>
            <p><strong>From:</strong> <?php echo sanitize($flight['origin_city']) . ' (' . sanitize($flight['origin_code']) . ')'; ?></p>
            <p><strong>To:</strong> <?php echo sanitize($flight['destination_city']) . ' (' . sanitize($flight['destination_code']) . ')'; ?></p>
            <p><strong>Depart:</strong> <?php echo $departure_dt->format('D, M j, Y H:i'); ?></p>
            <p><strong>Arrive:</strong> <?php echo $arrival_dt->format('D, M j, Y H:i'); ?></p>
        </div>
        <hr>
        <div>
             <h5 class="text-lg font-semibold mb-2">Passenger Details</h5>
             <ul class="list-group list-group-flush">
                <?php foreach ($passenger_details as $index => $pax): ?>
                    <li class="list-group-item">
                        <strong>Passenger <?php echo $index; ?>:</strong> <?php echo sanitize($pax['full_name']); ?>
                        <?php if ($pax['dob']): ?>
                           (DOB: <?php echo sanitize($pax['dob']); ?>)
                        <?php endif; ?>
                    </li>
                 <?php endforeach; ?>
             </ul>
        </div>
         <hr>
        <div>
             <h5 class="text-lg font-semibold mb-2">Price Summary</h5>
             <p><strong>Passengers:</strong> <?php echo sanitize($num_passengers); ?></p>
             <p class="font-bold text-xl text-green-600">Total Price: Rs.<?php echo number_format($total_price, 2); ?></p>
        </div>

        <div class="alert alert-warning mt-4">
             <i class="fas fa-exclamation-triangle me-1"></i> By clicking "Confirm & Book", you agree to our (non-existent) terms and conditions. Payment is simulated.
        </div>

        <form action="process_booking.php" method="POST" class="mt-4">
             <?php // Pass all data again using hidden fields ?>
            <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
            <input type="hidden" name="num_passengers" value="<?php echo $num_passengers; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
            <?php foreach ($passenger_details as $index => $pax): ?>
                <input type="hidden" name="passengers[<?php echo $index; ?>][full_name]" value="<?php echo sanitize($pax['full_name']); ?>">
                <input type="hidden" name="passengers[<?php echo $index; ?>][dob]" value="<?php echo sanitize($pax['dob'] ?? ''); ?>">
            <?php endforeach; ?>
            <?php // Simple CSRF token - needed if implemented earlier ?>

            <div class="d-flex justify-content-between">
                 <?php // Go back to step 1 preserving data (more complex, skip for exam) ?>
                 <a href="booking_step1.php?flight_id=<?php echo $flight_id; ?>&pax=<?php echo $num_passengers; ?>" class="btn btn-outline-secondary">
                     <i class="fas fa-edit me-1"></i> Edit Details
                 </a>
                 <button type="submit" class="btn btn-success btn-lg px-5 confirm-action" data-confirm-message="Confirm booking? This will simulate payment.">
                    <i class="fas fa-check-circle me-1"></i> Confirm & Book
                 </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>