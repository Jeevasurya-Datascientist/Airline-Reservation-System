<?php
require_once 'db.php';
require_once 'functions.php';

// Get search parameters (use GET method from form)
$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$departure_date = trim($_GET['departure_date'] ?? '');
$passengers = filter_var($_GET['passengers'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);

// --- Basic Validation ---
$errors = [];
if (empty($origin)) $errors[] = "Origin airport is required.";
if (empty($destination)) $errors[] = "Destination airport is required.";
if (empty($departure_date)) {
    $errors[] = "Departure date is required.";
} else {
    // Basic date format check (more robust validation needed in real app)
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $departure_date)) {
        $errors[] = "Invalid departure date format. Use<ctrl3348>-MM-DD.";
    }
}
if ($passengers === false || $passengers < 1) {
    $errors[] = "Invalid number of passengers (must be 1-9).";
    $passengers = 1; // Default to 1 if invalid
}
if ($origin === $destination && !empty($origin)) {
    $errors[] = "Origin and destination cannot be the same.";
}

$flights = [];
if (empty($errors)) {
    // --- Database Query ---
    try {
        $searchDateStart = $departure_date . ' 00:00:00';
        $searchDateEnd = $departure_date . ' 23:59:59';

        $sql = "SELECT
                    f.id, f.flight_number, f.departure_time, f.arrival_time, f.price, f.seats_available,
                    a.name AS airline_name, a.code AS airline_code, a.logo AS airline_logo,
                    orig.city AS origin_city, dest.city AS destination_city,
                    TIMESTAMPDIFF(MINUTE, f.departure_time, f.arrival_time) AS duration_minutes
                FROM flights f
                JOIN airlines a ON f.airline_code = a.code
                JOIN airports orig ON f.origin_code = orig.code
                JOIN airports dest ON f.destination_code = dest.code
                WHERE
                    f.origin_code = :origin
                    AND f.destination_code = :destination
                    AND f.departure_time BETWEEN :date_start AND :date_end
                    AND f.seats_available >= :passengers
                ORDER BY f.departure_time ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':origin', $origin);
        $stmt->bindParam(':destination', $destination);
        $stmt->bindParam(':date_start', $searchDateStart);
        $stmt->bindParam(':date_end', $searchDateEnd);
        $stmt->bindParam(':passengers', $passengers, PDO::PARAM_INT);
        $stmt->execute();
        $flights = $stmt->fetchAll();

    } catch (PDOException $e) {
        $errors[] = "Database error searching flights. " . $e->getMessage(); // Show detailed error for exam debug
        // error_log("Search Flight Error: " . $e->getMessage()); // Log properly in real app
    }
}

$pageTitle = "Flight Results";
// Instead of requiring header.php and footer.php, let's include basic HTML structure here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($pageTitle); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Basic custom styles and animations */
        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 60px;
        }
        .logo-container img {
            transition: transform 0.3s ease-in-out;
        }
        .logo-container img:hover {
            transform: scale(1.1);
        }
        .flight-card {
            border: 1px solid #f0f0f0;
            border-radius: 0.25rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .flight-card .card-body {
            padding: 0;
        }
        .flight-card .row {
            margin: 0;
        }
        .flight-card [class*="col-"] {
            padding: 1rem;
        }
        .bg-gray-50 {
            background-color: #f9f9f9;
        }
        .border-end-md {
            border-right: 1px solid #dee2e6;
        }
        .border-start-md {
            border-left: 1px solid #dee2e6;
        }
        @media (max-width: 767.98px) {
            .border-end-md { border-right: none !important; border-bottom: 1px solid #dee2e6; }
            .border-start-md { border-left: none !important; border-top: 1px solid #dee2e6; }
        }
        .bg-blue-50 {
            background-color: #e0f2f7; /* Light blue */
        }
        .text-green-600 {
            color: #198754; /* Bootstrap success color */
        }
        .text-orange-600 {
            color: #fd7e14; /* Bootstrap warning color */
        }
        .flight-card:nth-child(odd) {
            animation-delay: 0.1s;
        }
        .flight-card:nth-child(even) {
            animation-delay: 0.2s;
        }
        .btn-success {
            transition: transform 0.2s ease-in-out;
        }
        .btn-success:hover {
            transform: scale(1.05);
        }
        .btn-outline-secondary {
            transition: transform 0.2s ease-in-out;
        }
        .btn-outline-secondary:hover {
            transform: scale(1.03);
        }
        .flight-card .row .col-md-7 .d-flex > div.text-center > div.position-relative i.fas.fa-plane {
        color: #indigo-500;
        position: absolute;
        left: 50%;
        top: 50%; /* Original value - adjust this */
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 0px 4px; /* Adjust padding if needed */
        font-size: 0.8em; /* Adjust size if needed */
    }
    </style>
    </head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-indigo-800 animate__animated animate__slideInDown">Flight Results</h1>
        <p class="mb-4 text-gray-600 animate__animated animate__fadeIn animate__delay-1s">Showing flights from <strong><?php echo sanitize($origin); ?></strong> to <strong><?php echo sanitize($destination); ?></strong> on <strong><?php echo sanitize($departure_date); ?></strong> for <strong><?php echo sanitize($passengers); ?></strong> passenger(s).</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <h5 class="alert-heading">Search Error!</h5>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo sanitize($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="index.php" class="btn btn-sm btn-outline-danger mt-2 animate__animated animate__fadeIn animate__delay-2s">Try Search Again</a>
            </div>
        <?php endif; ?>

        <?php if (empty($errors) && empty($flights)): ?>
            <div class="alert alert-warning text-center animate__animated animate__pulse">
                <h4 class="alert-heading"><i class="fas fa-info-circle"></i> No Flights Found</h4>
                <p>We couldn't find any flights matching your criteria for this date. Please try different dates or airports.</p>
                <a href="index.php" class="btn btn-primary mt-2 animate__animated animate__fadeIn animate__delay-1s">New Search</a>
            </div>
        <?php elseif (!empty($flights)): ?>
            <div class="space-y-4">
                <?php foreach ($flights as $flight):
                    $total_price = $flight['price'] * $passengers;
                    $duration_hours = floor($flight['duration_minutes'] / 60);
                    $duration_mins = $flight['duration_minutes'] % 60;
                    $departure_dt = new DateTime($flight['departure_time']);
                    $arrival_dt = new DateTime($flight['arrival_time']);
                ?>
                    <div class="card flight-card shadow-sm border-light overflow-hidden animate__animated animate__fadeInUp">
                        <div class="card-body p-0">
                            <div class="row no-gutters align-items-stretch"> <?php // Bootstrap class for equal height columns ?>
                                <div class="col-md-2 p-3 border-end-md bg-gray-50 d-flex flex-column align-items-center justify-content-center">
                                    <div class="logo-container animate__animated animate__fadeIn">
                                        <?php if ($flight['airline_logo']): ?>
                                            <img src="images/<?php echo sanitize($flight['airline_logo']); ?>"
                                                 alt="<?php echo sanitize($flight['airline_name']); ?> Logo"
                                                 style="max-height: 100%; max-width: 100%; object-fit: contain; display: block; margin: auto;">
                                        <?php endif; ?>
                                    </div>
                                    <span class="d-block text-sm font-weight-semibold text-center mt-2 animate__animated animate__fadeIn animate__delay-0-5s">
                                        <?php echo sanitize($flight['airline_name']); ?>
                                    </span>
                                    <span class="d-block text-xs text-muted text-center animate__animated animate__fadeIn animate__delay-0-7s">
                                        <?php echo sanitize($flight['flight_number']); ?>
                                    </span>
                                </div>

                                <div class="col-md-7 p-3 d-flex flex-column justify-content-center"> <?php // Bootstrap flex for vertical centering ?>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="text-start animate__animated animate__fadeInLeft animate__delay-0-3s">
                                            <strong class="text-lg font-weight-semibold"><?php echo $departure_dt->format('H:i'); ?></strong>
                                            <span class="d-block text-sm text-muted"><?php echo sanitize($origin); ?></span>
                                            <span class="d-block text-xs text-muted"><?php echo sanitize($flight['origin_city']); ?></span>
                                        </div>
                                        <div class="text-center px-2 flex-grow-1 animate__animated animate__fadeIn animate__delay-0-4s">
                                            <span class="text-muted text-xs d-block"><?php echo $duration_hours . 'h ' . $duration_mins . 'm'; ?></span>
                                            <div class="position-relative my-1">
                                                <hr class="border-secondary">
                                                <i class="fas fa-plane text-indigo-500 position-absolute top-50 start-50 translate-middle bg-white px-1 text-sm"></i>
                                            </div>
                                            <span class="text-muted text-xs d-block">Direct</span> <?php // Placeholder ?>
                                        </div>
                                        <div class="text-end animate__animated animate__fadeInRight animate__delay-0-3s">
                                            <strong class="text-lg font-weight-semibold"><?php echo $arrival_dt->format('H:i'); ?></strong>
                                            <span class="d-block text-sm text-muted"><?php echo sanitize($destination); ?></span>
                                            <span class="d-block text-xs text-muted"><?php echo sanitize($flight['destination_city']); ?></span>
                                        </div>
                                    </div>
                                    <div class="text-center text-muted text-xs mt-1 animate__animated animate__fadeIn animate__delay-0-6s"><?php echo $departure_dt->format('D, M j, Y'); ?></div>
                                </div>

                                <div class="col-md-3 text-center p-3 bg-blue-50 border-start-md d-flex flex-column justify-content-center align-items-center animate__animated animate__fadeIn animate__delay-0-8s">
                                <strong class="block text-xl text-green-600 mb-1"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($total_price, 2); ?></strong>
                                    <span class="text-muted text-xs d-block mb-2">Total for <?php echo sanitize($passengers); ?></span>
                                    <form action="booking_step1.php" method="GET" class="w-100">
                                        <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                        <input type="hidden" name="pax" value="<?php echo sanitize($passengers); ?>">
                                        <button type="submit" class="btn btn-success w-100">
                                            Select <i class="fas fa-chevron-right ms-1 text-xs"></i>
                                        </button>
                                    </form>
                                    <span class="text-xs text-orange-600 d-block mt-1"><?php echo $flight['seats_available']; ?> seats left</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 text-center animate__animated animate__fadeInUp animate__delay-1s">
                <a href="index.php" class="btn btn-outline-secondary"> <i class="fas fa-arrow-left me-1"></i> Back to Search</a>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    </body>
</html>