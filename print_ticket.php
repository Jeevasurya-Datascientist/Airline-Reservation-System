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
    redirect('my_bookings.php');
}

// Fetch comprehensive booking details
try {
    $stmt = $pdo->prepare("SELECT b.*, f.flight_number, f.departure_time, f.arrival_time, 
                               f.origin_code, f.destination_code, a.name as airline_name, 
                               a.logo as airline_logo, orig.city as origin_city, 
                               orig.name as origin_airport, dest.city as destination_city, 
                               dest.name as destination_airport
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
    
    // Fetch passenger details
    $stmt = $pdo->prepare("SELECT * FROM passengers WHERE booking_id = :booking_id ORDER BY id");
    $stmt->bindParam(':booking_id', $booking['id'], PDO::PARAM_INT);
    $stmt->execute();
    $passengers = $stmt->fetchAll();

} catch (PDOException $e) {
    setFlashMessage('error', 'Could not retrieve booking details: ' . $e->getMessage());
    redirect('my_bookings.php');
}

$departure_dt = new DateTime($booking['departure_time']);
$arrival_dt = new DateTime($booking['arrival_time']);

// Calculate flight duration
$interval = $departure_dt->diff($arrival_dt);
$hours = $interval->h + ($interval->days * 24);
$minutes = $interval->i;
$duration = "{$hours}h {$minutes}m";

// Format dates for display
$departure_date = $departure_dt->format('D, M j, Y');
$departure_time = $departure_dt->format('H:i');
$arrival_date = $arrival_dt->format('D, M j, Y');
$arrival_time = $arrival_dt->format('H:i');

$pageTitle = "E-Ticket: " . $booking['booking_ref'];
// For print page, we need a special header with print functionality
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - ExamFlight</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0ea5e9; /* Sky-500 */
            --primary-dark: #0284c7;  /* Sky-600 */
            --primary-light: #38bdf8; /* Sky-400 */
            --accent-color: #14b8a6;  /* Teal-500 */
            --highlight: #fbbf24;     /* Amber-400 */
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .ticket-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: #ffffff;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .ticket-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 1.5rem;
            position: relative;
        }

        .ticket-body {
            padding: 2rem;
        }

        .flight-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .flight-route {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .flight-route-line {
            flex-grow: 1;
            height: 2px;
            background-color: #e5e7eb;
            margin: 0 1rem;
            position: relative;
        }

        .flight-route-line::after {
            content: '✈️';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.25rem;
        }

        .airport-code {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .airport-name {
            font-size: 0.875rem;
            color: #64748b;
        }

        .flight-time {
            text-align: center;
        }

        .departure-time, .arrival-time {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .flight-date {
            font-size: 0.875rem;
            color: #64748b;
        }

        .flight-duration {
            font-size: 0.875rem;
            color: #64748b;
            text-align: center;
            margin-top: 0.5rem;
        }

        .booking-details {
            background-color: #f1f5f9;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .booking-ref {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: #dc2626;
            letter-spacing: 1px;
        }

        .passenger-list {
            margin-top: 1.5rem;
        }

        .passenger-item {
            background-color: #f8fafc;
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .barcode {
            text-align: center;
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f1f5f9;
            border-radius: 0.5rem;
        }

        .barcode-img {
            height: 70px;
            max-width: 100%;
        }

        .logo {
            max-height: 40px;
            margin-right: 1rem;
        }

        .print-actions {
            text-align: center;
            margin: 2rem 0;
        }

        .print-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .print-btn:hover {
            background-color: #0d9488;
            transform: translateY(-2px);
        }

        .return-btn {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 1rem;
        }

        .return-btn:hover {
            background-color: rgba(14, 165, 233, 0.1);
        }

        .airline-info {
            display: flex;
            align-items: center;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 1.5rem 0;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 5px;
        }

        .important-note {
            font-size: 0.875rem;
            color: #64748b;
            padding: 1rem;
            background-color: #f8fafc;
            border-left: 4px solid var(--highlight);
            margin-top: 1.5rem;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .contact-info {
            font-size: 0.875rem;
            color: #64748b;
            text-align: center;
            margin-top: 1.5rem;
        }

        /* Specific print styles */
        @media print {
            body {
                background-color: white;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .print-actions {
                display: none;
            }
            
            .ticket-container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }

            a {
                text-decoration: none;
                color: inherit;
            }

            .important-note {
                border-left-color: #fbbf24 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-actions">
            <button onclick="window.print()" class="print-btn">
                <i class="fas fa-print"></i> Print E-Ticket
            </button>
            <a href="my_bookings.php" class="return-btn">Return to My Bookings</a>
        </div>
        
        <div class="ticket-container">
            <div class="ticket-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">E-Ticket / Boarding Pass</h1>
                    <div class="airline-info">
                    <img src="images/<?php echo sanitize($booking['airline_logo']); ?>"
     alt="<?php echo sanitize($booking['airline_name']); ?> Logo"
     style="max-height: 100%; max-width: 100%; object-fit: contain; display: block; margin: auto;">
                    </div>
                </div>
            </div>
            
            <div class="ticket-body">
                <div class="booking-details">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Booking Reference</small>
                                <div class="booking-ref"><?php echo sanitize($booking['booking_ref']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Flight</small>
                                <div class="h5 mb-0"><?php echo sanitize($booking['airline_name']) . ' ' . sanitize($booking['flight_number']); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Status</small>
                                <div class="h6 mb-0"><span class="badge bg-success"><?php echo sanitize($booking['status']); ?></span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Class</small>
                                 Economy 
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flight-route">
                    <div class="text-center">
                        <div class="airport-code"><?php echo sanitize($booking['origin_code']); ?></div>
                        <div class="airport-name"><?php echo sanitize($booking['origin_airport']); ?></div>
                        <div class="airport-city"><?php echo sanitize($booking['origin_city']); ?></div>
                        <div class="departure-time"><?php echo $departure_time; ?></div>
                        <div class="flight-date"><?php echo $departure_date; ?></div>
                    </div>
                    
                    <div class="flight-route-line"></div>
                    
                    <div class="text-center">
                        <div class="airport-code"><?php echo sanitize($booking['destination_code']); ?></div>
                        <div class="airport-name"><?php echo sanitize($booking['destination_airport']); ?></div>
                        <div class="airport-city"><?php echo sanitize($booking['destination_city']); ?></div>
                        <div class="arrival-time"><?php echo $arrival_time; ?></div>
                        <div class="flight-date"><?php echo $arrival_date; ?></div>
                    </div>
                </div>
                
                <div class="flight-duration text-center">
                    <span>Flight Duration: <?php echo $duration; ?></span>
                </div>
                
                <div class="divider"></div>
                
                <h5 class="mb-3">Passenger Information</h5>
<div class="passenger-list">
    <?php foreach ($passengers as $index => $passenger): ?>
    <div class="passenger-item">
        <strong><?php echo $index + 1; ?>:</strong> <?php echo sanitize($passenger['full_name']); ?>
    </div>
    <?php endforeach; ?>
</div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                    <div class="barcode text-center">
    <img src="images/barcode.gif" alt="Barcode" class="barcode-img">
    <div class="small text-muted mt-2"><?php echo sanitize($booking['booking_ref']); ?></div>
</div>
                    </div>
                    <div class="col-md-6">
                    <div class="text-center">
    <div class="qr-code">
        <img src="images/qr-code.png" alt="QR Code" style="width: 100%; height: 100%;">
    </div>
    <div class="small text-muted mt-2">Scan for mobile boarding</div>
</div>
                    </div>
                </div>
                
                <div class="contact-info mt-4">
                    <p>For any assistance, contact ExamFlight Customer Support:<br>
                    Email: support@examflight.com | Phone: +1-800-123-4567</p>
                </div>
                
                <div class="mt-4 text-center">
                    <p>Amount Paid: <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when the page loads (optional)
         window.onload = function() {
             window.print();
        };
    </script>
</body>
</html>