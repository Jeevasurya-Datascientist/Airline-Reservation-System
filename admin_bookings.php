
<?php
require_once 'db.php';
require_once 'functions.php';

// Require admin login
if (!isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('login.php');
}

$bookings = [];
try {
    // Fetch all bookings with user and flight info
     $sql = "SELECT
                b.id, b.booking_ref, b.booking_time, b.num_passengers, b.total_price, b.status,
                u.email AS user_email, u.full_name AS user_name,
                f.flight_number, f.departure_time,
                a.name AS airline_name,
                orig.code AS origin_code, dest.code AS destination_code
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN flights f ON b.flight_id = f.id
            JOIN airlines a ON f.airline_code = a.code
            JOIN airports orig ON f.origin_code = orig.code
            JOIN airports dest ON f.destination_code = dest.code
            ORDER BY b.booking_time DESC";
    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll();

} catch (PDOException $e) {
     setFlashMessage('error', 'Could not retrieve all bookings: ' . $e->getMessage());
}


$pageTitle = "Admin - All Bookings";
require_once 'header.php';
?>

<h1 class="text-3xl font-bold mb-4 text-indigo-800">Admin - All Bookings</h1>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info">No bookings found in the system yet.</div>
<?php else: ?>
    <div class="table-responsive shadow-sm rounded border">
        <table class="table table-striped table-hover mb-0">
            <thead class="bg-gray-200 text-gray-700 text-sm uppercase">
                <tr>
                    <th>Ref</th>
                    <th>User</th>
                    <th>Flight</th>
                    <th>Route</th>
                    <th>Depart</th>
                    <th>Pax</th>
                    <th>Price</th>
                    <th>Booked On</th>
                    <th>Status</th>
                    <?php //<th>Actions</th> ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking):
                    $departure_dt = new DateTime($booking['departure_time']);
                    $booking_dt = new DateTime($booking['booking_time']);
                ?>
                    <tr>
                        <td class="font-mono text-red-600"><?php echo sanitize($booking['booking_ref']); ?></td>
                        <td><?php echo sanitize($booking['user_name']); ?><br><small class="text-muted"><?php echo sanitize($booking['user_email']); ?></small></td>
                        <td><?php echo sanitize($booking['airline_name']); ?> <?php echo sanitize($booking['flight_number']); ?></td>
                        <td><?php echo sanitize($booking['origin_code']); ?> <i class="fas fa-arrow-right text-xs"></i> <?php echo sanitize($booking['destination_code']); ?></td>
                        <td><?php echo $departure_dt->format('M j, Y H:i'); ?></td>
                        <td><?php echo sanitize($booking['num_passengers']); ?></td>
                        <td>€<?php echo number_format($booking['total_price'], 2); ?></td>
                        <td><?php echo $booking_dt->format('M j, Y'); ?></td>
                        <td>
                             <?php $status_badge = $booking['status'] === 'Confirmed' ? 'bg-success' : 'bg-danger'; ?>
                             <span class="badge <?php echo $status_badge; ?>"><?php echo sanitize($booking['status']); ?></span>
                        </td>
                        <?php /* Optional Actions Column
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-info" title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-sm btn-outline-danger confirm-action" data-confirm-message="Cancel booking <?php echo sanitize($booking['booking_ref']); ?>?" title="Cancel"><i class="fas fa-times"></i></a>
                        </td>
                         */ ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
     <p class="text-muted mt-2 text-sm">Total Bookings: <?php echo count($bookings); ?></p>
<?php endif; ?>


<?php require_once 'footer.php'; ?>