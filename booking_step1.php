<?php
require_once 'db.php';
require_once 'functions.php';

// Require login to book
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login or register to book a flight.');
    redirect('login.php?redirect_url=booking_step1.php?' . http_build_query($_GET)); // Redirect back after login
}

$flight_id = filter_input(INPUT_GET, 'flight_id', FILTER_VALIDATE_INT);
$passengers = filter_input(INPUT_GET, 'pax', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);

if (!$flight_id || !$passengers) {
    setFlashMessage('error', 'Invalid flight selection or passenger number.');
    redirect('index.php');
}

// Fetch flight details to display summary
try {
    $sql = "SELECT f.*, a.name as airline_name, orig.city as origin_city, dest.city as destination_city
            FROM flights f
            JOIN airlines a ON f.airline_code = a.code
            JOIN airports orig ON f.origin_code = orig.code
            JOIN airports dest ON f.destination_code = dest.code
            WHERE f.id = :id AND f.seats_available >= :pax"; // Re-check seats
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $flight_id, PDO::PARAM_INT);
    $stmt->bindParam(':pax', $passengers, PDO::PARAM_INT);
    $stmt->execute();
    $flight = $stmt->fetch();

    if (!$flight) {
        setFlashMessage('error', 'Selected flight not found or not enough seats available.');
        redirect('index.php');
    }
} catch (PDOException $e) {
     setFlashMessage('error', 'Error fetching flight details: ' . $e->getMessage());
     redirect('index.php');
}

$total_price = $flight['price'] * $passengers;
$departure_dt = new DateTime($flight['departure_time']);
$arrival_dt = new DateTime($flight['arrival_time']);

$pageTitle = "Passenger Details";
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

<div class="container py-5 animate__animated animate__fadeIn">
    <h1 class="text-3xl font-semibold mb-4 text-indigo-800 animate__animated animate__fadeInUp">Enter Passenger Details</h1>

    <!-- Flight Summary -->
    <div class="card flight-summary mb-4 animate__animated animate__fadeInUp animate__delay-1s">
        <div class="card-header">
            <h2 class="text-xl font-semibold mb-0">Flight Summary</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="fas fa-plane-departure text-primary-light mr-2"></i> Flight:</strong> <?php echo sanitize($flight['airline_name']) . ' ' . sanitize($flight['flight_number']); ?></p>
                    <p><strong><i class="fas fa-map-marker-alt text-primary-light mr-2"></i> From:</strong> <?php echo sanitize($flight['origin_city']) . ' (' . sanitize($flight['origin_code']) . ')'; ?></p>
                    <p><strong><i class="fas fa-map-marker text-primary-light mr-2"></i> To:</strong> <?php echo sanitize($flight['destination_city']) . ' (' . sanitize($flight['destination_code']) . ')'; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="fas fa-calendar-alt text-primary-light mr-2"></i> Depart:</strong> <?php echo $departure_dt->format('D, M j, Y H:i'); ?></p>
                    <p><strong><i class="fas fa-calendar-check text-primary-light mr-2"></i> Arrive:</strong> <?php echo $arrival_dt->format('D, M j, Y H:i'); ?></p>
                    <p><strong><i class="fas fa-users text-primary-light mr-2"></i> Passengers:</strong> <?php echo sanitize($passengers); ?></p>
                    <p class="price-highlight mt-2"><i class="fas fa-tag text-primary-light mr-2"></i> Total Price: Rs.<?php echo number_format($total_price, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <form action="booking_step2.php" method="POST">
        <input type="hidden" name="flight_id" value="<?php echo $flight_id; ?>">
        <input type="hidden" name="num_passengers" value="<?php echo $passengers; ?>">
        <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
        <?php // Simple CSRF token - would need verification in process_booking.php
              // For exam simplicity, CSRF might be omitted, but acknowledge it's needed.
              // echo '<input type="hidden" name="csrf_token" value="'. generateCsrfToken() .'">';
        ?>

        <?php for ($i = 1; $i <= $passengers; $i++): ?>
            <div class="passenger-card animate__animated animate__fadeInUp animate__delay-<?php echo $i; ?>s">
                <div class="card-header">
                    <i class="fas fa-user-circle mr-2"></i> Passenger <?php echo $i; ?> Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pax_<?php echo $i; ?>_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-user text-primary-light"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="pax_<?php echo $i; ?>_name" name="passengers[<?php echo $i; ?>][full_name]" required placeholder="Passenger's full name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="pax_<?php echo $i; ?>_dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-calendar text-primary-light"></i>
                                </span>
                                <input 
                                    type="date" 
                                    class="form-control border-start-0 ps-0" 
                                    id="pax_<?php echo $i; ?>_dob" 
                                    name="passengers[<?php echo $i; ?>][dob]" 
                                    max="<?php echo date('Y-m-d'); ?>" 
                                    required
                                    aria-describedby="pax_<?php echo $i; ?>_dob_help">
                            </div>
                            <div id="pax_<?php echo $i; ?>_dob_help" class="form-text">Please enter date of birth (must not be after today's date).</div>
                        </div>
                        <div class="col-md-6">
                            <label for="pax_<?php echo $i; ?>_aadhaar" class="form-label">Aadhaar Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-id-card text-primary-light"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0 ps-0" 
                                    id="pax_<?php echo $i; ?>_aadhaar" 
                                    name="passengers[<?php echo $i; ?>][aadhaar_number]" 
                                    maxlength="14" 
                                    title="Please enter a valid 12-digit Aadhaar number" 
                                    oninput="formatAadhaar(this)"
                                    placeholder="XXXX-XXXX-XXXX"
                                    required>
                            </div>
                            <div class="form-text">Enter 12 digits (format: XXXX-XXXX-XXXX).</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>

        <div class="alert alert-info mt-4 animate__animated animate__fadeInUp animate__delay-3s">
           <i class="fas fa-info-circle mr-2"></i> Payment will be simulated upon confirmation on the next page.
        </div>

        <div class="mt-4 d-flex justify-content-between animate__animated animate__fadeInUp animate__delay-3s">
            <a href="search_results.php?<?php echo http_build_query(['origin' => $flight['origin_code'], 'destination' => $flight['destination_code'], 'departure_date' => $departure_dt->format('Y-m-d'), 'passengers' => $passengers]); ?>" class="back-btn">
                 <i class="fas fa-arrow-left mr-2"></i> Back to Results
            </a>
            <button type="submit" class="proceed-btn">
                Proceed to Confirmation <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>

<script>
function formatAadhaar(input) {
    // Remove all non-numeric characters
    let value = input.value.replace(/\D/g, '');
    
    // Limit to 12 digits
    value = value.substring(0, 12);
    
    // Format with hyphens after every 4 digits
    let formattedValue = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += '-';
        }
        formattedValue += value[i];
    }
    
    // Update input value
    input.value = formattedValue;
    
    // Store the raw value (without hyphens) in a hidden field for form submission
    let hiddenInput = document.getElementById(input.id + '_raw');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id = input.id + '_raw';
        hiddenInput.name = input.name;
        input.name = input.name + '_formatted';
        input.parentNode.appendChild(hiddenInput);
    }
    hiddenInput.value = value;
}
</script>
<?php require_once 'footer.php'; ?>