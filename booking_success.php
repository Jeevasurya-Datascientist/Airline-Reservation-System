<?php
require_once 'db.php';
require_once 'functions.php';

// Require login
if (!isLoggedIn()) {
    redirect('login.php');
}

$booking_ref = trim($_GET['ref'] ?? '');

if (empty($booking_ref)) {
    setFlashMessage('error', 'No booking reference provided.');
    redirect('index.php');
}

// Fetch basic booking details to confirm
try {
    $stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.departure_time, a.name as airline_name,
                                orig.city as origin_city, dest.city as destination_city
                           FROM bookings b
                           JOIN flights f ON b.flight_id = f.id
                           JOIN airlines a ON f.airline_code = a.code
                           JOIN airports orig ON f.origin_code = orig.code
                           JOIN airports dest ON f.destination_code = dest.code
                           WHERE b.booking_ref = :ref AND b.user_id = :user_id");
    $stmt->bindParam(':ref', $booking_ref);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch();

    if (!$booking) {
        setFlashMessage('error', 'Booking reference not found or does not belong to you.');
        redirect('my_bookings.php');
    }

} catch (PDOException $e) {
     setFlashMessage('error', 'Could not retrieve booking details: ' . $e->getMessage());
     redirect('my_bookings.php');
}

$departure_dt = new DateTime($booking['departure_time']);

$pageTitle = "Booking Successful";
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
    
    /* Print button styling */
    .btn-print {
        background-color: #14b8a6; /* Teal-500 */
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .btn-print:hover {
        background-color: #0d9488; /* Teal-600 */
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(20, 184, 166, 0.3);
    }
    
    .btn-print i {
        font-size: 0.9rem;
    }
</style>
<div class="text-center max-w-2xl mx-auto">
    <i class="fas fa-check-circle text-green-500 text-6xl mb-3"></i>
    <h1 class="text-3xl font-bold mb-2 text-gray-800">Booking Confirmed!</h1>
    <p class="text-gray-600 mb-4">Thank you for booking with ExamFlight. Your flight is confirmed.</p>

    <div class="card text-start shadow-sm border border-gray-200">
        <div class="card-header bg-gray-100 font-semibold">
            Your Booking Details
        </div>
        <div class="card-body space-y-2 text-sm">
            <p><strong>Booking Reference:</strong> <span class="font-mono text-lg text-red-600"><?php echo sanitize($booking['booking_ref']); ?></span></p>
            <p><strong>Flight:</strong> <?php echo sanitize($booking['airline_name']) . ' ' . sanitize($booking['flight_number']); ?></p>
            <p><strong>Route:</strong> <?php echo sanitize($booking['origin_city']); ?> to <?php echo sanitize($booking['destination_city']); ?></p>
            <p><strong>Departure:</strong> <?php echo $departure_dt->format('D, M j, Y H:i'); ?></p>
            <p><strong>Passengers:</strong> <?php echo sanitize($booking['num_passengers']); ?></p>
            <p><strong>Total Price:</strong> Rs.<?php echo number_format($booking['total_price'], 2); ?></p>
            <p><strong>Status:</strong> <span class="badge bg-success"><?php echo sanitize($booking['status']); ?></span></p>
        </div>
    </div>

    <div class="mt-5">
        <a href="print_ticket.php?ref=<?php echo sanitize($booking['booking_ref']); ?>" class="btn btn-print mx-2">
            <i class="fas fa-print"></i> Print Ticket
        </a>
        <a href="my_bookings.php" class="btn btn-primary mx-2">View My Bookings</a>
        <a href="index.php" class="btn btn-outline-secondary mx-2">Book Another Flight</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>