<?php
require_once 'db.php';
require_once 'functions.php';

// Require login
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please log in to view your bookings.');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$bookings = [];

try {
    $sql = "SELECT
                b.id, b.booking_ref, b.booking_time, b.num_passengers, b.total_price, b.status,
                f.flight_number, f.departure_time,
                a.name AS airline_name, a.logo AS airline_logo,
                orig.code AS origin_code, orig.city AS origin_city,
                dest.code AS destination_code, dest.city AS destination_city
            FROM bookings b
            JOIN flights f ON b.flight_id = f.id
            JOIN airlines a ON f.airline_code = a.code
            JOIN airports orig ON f.origin_code = orig.code
            JOIN airports dest ON f.destination_code = dest.code
            WHERE b.user_id = :user_id
            ORDER BY b.booking_time DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlashMessage('error', 'Could not retrieve bookings: ' . $e->getMessage());
    // No redirect, just show error on the page
}


$pageTitle = "My Bookings";
require_once 'header.php';
?>

<style>
    :root {
        --primary-color: #0ea5e9; /* Sky-500 */
        --primary-dark: #0284c7;  /* Sky-600 */
        --primary-light: #38bdf8; /* Sky-400 */
        --accent-color: #14b8a6;  /* Teal-500 */
        --highlight: #fbbf24;     /* Amber-400 */
    }
    
    .page-header {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-bottom: 2rem;
    }
    
    .booking-card {
        background-color: #ffffff;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }
    
    .booking-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .booking-header {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .booking-body {
        padding: 1.5rem;
    }
    
    .booking-footer {
        padding: 1rem;
        background-color: #f8fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f1f5f9;
    }
    
    .flight-route {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .flight-route-line {
        flex-grow: 1;
        height: 2px;
        background-color: #e5e7eb;
        margin: 0 0.5rem;
        position: relative;
    }
    
    .flight-route-line::after {
        content: '✈️';
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1rem;
    }
    
    .airport-code {
        font-weight: 700;
        color: #1e293b;
    }
    
    .booking-ref {
        font-family: 'Courier New', monospace;
        font-weight: 700;
        color: #dc2626;
        letter-spacing: 1px;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-confirmed {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-pending {
        background-color: #fff7ed;
        color: #c2410c;
    }
    
    .status-cancelled {
        background-color: #fef2f2;
        color: #b91c1c;
    }
    
    .view-btn {
        background-color: var(--accent-color);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .view-btn:hover {
        background-color: #0d9488;
        transform: translateY(-2px);
    }
    
    .airline-logo {
        max-height: 30px;
        margin-right: 0.5rem;
    }
    
    .no-bookings-container {
        background-color: #f1f5f9;
        border-radius: 0.5rem;
        padding: 3rem;
        text-align: center;
        margin: 2rem 0;
    }
    
    .search-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin-top: 1rem;
    }
    
    .search-btn:hover {
        background-color: var(--primary-dark);
    }
</style>

<div class="container">
    <div class="page-header">
        <h1 class="h3 mb-0">My Bookings</h1>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="no-bookings-container">
            <h3 class="mb-3">You have no bookings yet</h3>
            <p class="mb-4">Start planning your next adventure with ExamFlight!</p>
            <a href="index.php" class="search-btn">
                <i class="fas fa-search mr-2"></i> Search Flights
            </a>
        </div>
    <?php else: ?>
        <div class="bookings-container">
            <?php foreach ($bookings as $booking):
                $departure_dt = new DateTime($booking['departure_time']);
                $booking_dt = new DateTime($booking['booking_time']);
                
                // Determine status badge class
                $status_class = 'status-confirmed';
                if ($booking['status'] == 'Pending') {
                    $status_class = 'status-pending';
                } elseif ($booking['status'] == 'Cancelled') {
                    $status_class = 'status-cancelled';
                }
            ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="d-flex align-items-center">
                            <?php if (!empty($booking['airline_logo'])): ?>
                            <img src="images/<?php echo sanitize($booking['airline_logo']); ?>" alt="<?php echo sanitize($booking['airline_name']); ?>" class="airline-logo">
                            <?php endif; ?>
                            <span class="h5 mb-0"><?php echo sanitize($booking['airline_name']); ?> <?php echo sanitize($booking['flight_number']); ?></span>
                        </div>
                        <span class="status-badge <?php echo $status_class; ?>"><?php echo sanitize($booking['status']); ?></span>
                    </div>
                    
                    <div class="booking-body">
                        <div class="flight-route">
                            <div class="text-center">
                                <div class="airport-code"><?php echo sanitize($booking['origin_code']); ?></div>
                                <div class="text-muted small"><?php echo sanitize($booking['origin_city']); ?></div>
                            </div>
                            
                            <div class="flight-route-line"></div>
                            
                            <div class="text-center">
                                <div class="airport-code"><?php echo sanitize($booking['destination_code']); ?></div>
                                <div class="text-muted small"><?php echo sanitize($booking['destination_city']); ?></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">Departing</small>
                                    <div><?php echo $departure_dt->format('D, M j, Y \a\t H:i'); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">Booking Reference</small>
                                    <div class="booking-ref"><?php echo sanitize($booking['booking_ref']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">Passengers</small>
                                    <div><?php echo sanitize($booking['num_passengers']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <small class="text-muted">Booked On</small>
                                    <div><?php echo $booking_dt->format('M j, Y'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-footer">
                        <div class="total-price font-weight-bold">Total: Rs.<?php echo number_format($booking['total_price'], 2); ?></div>
                        <a href="print_ticket.php?ref=<?php echo sanitize($booking['booking_ref']); ?>" class="view-btn">
                            <i class="fas fa-ticket-alt"></i> View E-Ticket
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>