<?php
$pageTitle = "My Profile";
$pdo = require_once 'db.php';
require_once 'header.php';

// Initialize variables
$userId = $_SESSION['user_id'] ?? null;
$userInfo = null;
$message = '';
$messageType = '';

// Check if user is logged in
if (!$userId) {
    header("Location: login.php");
    exit;
}

// Fetch user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch();
} catch (PDOException $e) {
    $message = "Could not load user information.";
    $messageType = "danger";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather form data
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $preferredAirport = trim($_POST['preferred_airport'] ?? '');
    
    try {
        // Update user information
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, preferred_airport = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $email, $phone, $preferredAirport, $userId]);
        
        $message = "Profile updated successfully!";
        $messageType = "success";
        
        // Refresh user info
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch();
    } catch (PDOException $e) {
        $message = "Error updating profile.";
        $messageType = "danger";
    }
}

// Fetch airports for preferred airport dropdown
try {
    $stmt = $pdo->query("SELECT code, name, city FROM airports ORDER BY city, name");
    $airports = $stmt->fetchAll();
} catch (PDOException $e) {
    $airports = []; // Handle error gracefully
}
?>

<div class="card shadow-lg border-0 rounded-lg overflow-hidden max-w-3xl mx-auto animate__animated animate__fadeIn animate__delay-1s">
    <div class="card-header p-4 animate__animated animate__slideInDown" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color))">
        <h1 class="text-3xl font-semibold mb-0 text-white d-flex align-items-center">
            <i class="fas fa-user-circle me-3 animate__animated animate__pulse animate__slow animate__infinite"></i> My Profile
        </h1>
    </div>
    
    <div class="card-body p-4 md:p-5 bg-gradient-to-b from-gray-50 to-white">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> animate__animated animate__fadeIn">
                <?php echo sanitize($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- User Stats Card -->
            <div class="col-md-4 mb-4 animate__animated animate__fadeInLeft animate__delay-1s">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle text-primary" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="text-xl font-semibold"><?php echo sanitize($userInfo['first_name'] . ' ' . $userInfo['last_name']); ?></h2>
                        <p class="text-muted"><?php echo sanitize($userInfo['email']); ?></p>
                        
                        <hr class="my-3">
                        
                        <div class="text-start mt-3">
                            <p><i class="fas fa-plane-departure me-2 text-primary"></i> <strong>Flights Booked:</strong> 
                                <span class="badge bg-primary rounded-pill ms-1">5</span>
                            </p>
                            <p><i class="fas fa-map-marker-alt me-2 text-accent"></i> <strong>Preferred Airport:</strong> 
                                <span class="badge bg-accent rounded-pill ms-1"><?php echo sanitize($userInfo['preferred_airport'] ?? 'None'); ?></span>
                            </p>
                            <p><i class="fas fa-award me-2 text-highlight"></i> <strong>Traveler Status:</strong> 
                                <span class="badge bg-highlight text-dark rounded-pill ms-1">Gold</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="col-md-8 animate__animated animate__fadeInRight animate__delay-1s">
                <form method="POST" class="space-y-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label fw-semibold text-primary">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color: rgba(14, 165, 233, 0.15); color: var(--primary-color); border-color: rgba(14, 165, 233, 0.3);">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg focus-ring" id="first_name" name="first_name" 
                                       value="<?php echo sanitize($userInfo['first_name'] ?? ''); ?>" required
                                       style="border-color: rgba(14, 165, 233, 0.3);">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="last_name" class="form-label fw-semibold" style="color: var(--primary-dark);">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color: rgba(2, 132, 199, 0.15); color: var(--primary-dark); border-color: rgba(2, 132, 199, 0.3);">
                                    <i class="fas fa-user-tag"></i>
                                </span>
                                <input type="text" class="form-control form-control-lg focus-ring" id="last_name" name="last_name" 
                                       value="<?php echo sanitize($userInfo['last_name'] ?? ''); ?>" required
                                       style="border-color: rgba(2, 132, 199, 0.3);">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold" style="color: var(--accent-color);">Email</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color: rgba(20, 184, 166, 0.15); color: var(--accent-color); border-color: rgba(20, 184, 166, 0.3);">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control form-control-lg focus-ring" id="email" name="email" 
                                       value="<?php echo sanitize($userInfo['email'] ?? ''); ?>" required
                                       style="border-color: rgba(20, 184, 166, 0.3);">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold" style="color: var(--primary-light);">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color: rgba(56, 189, 248, 0.15); color: var(--primary-light); border-color: rgba(56, 189, 248, 0.3);">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control form-control-lg focus-ring" id="phone" name="phone" 
                                       value="<?php echo sanitize($userInfo['phone'] ?? ''); ?>"
                                       style="border-color: rgba(56, 189, 248, 0.3);">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-12">
                            <label for="preferred_airport" class="form-label fw-semibold" style="color: var(--highlight);">Preferred Airport</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color: rgba(251, 191, 36, 0.15); color: var(--highlight); border-color: rgba(251, 191, 36, 0.3);">
                                    <i class="fas fa-plane"></i>
                                </span>
                                <select class="form-control form-control-lg focus-ring" id="preferred_airport" name="preferred_airport"
                                       style="border-color: rgba(251, 191, 36, 0.3);">
                                    <option value="">-- Select Preferred Airport --</option>
                                    <?php foreach ($airports as $airport): ?>
                                        <option value="<?php echo sanitize($airport['code']); ?>" 
                                            <?php echo ($userInfo['preferred_airport'] === $airport['code']) ? 'selected' : ''; ?>>
                                            <?php echo sanitize($airport['city']) . ' (' . sanitize($airport['code']) . ' - ' . sanitize($airport['name']) . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-4 animate__animated animate__fadeInUp animate__delay-3s">
                        <button type="submit" class="btn btn-lg px-5 py-3 update-profile-btn">
                            <i class="fas fa-save me-2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Trip History Section -->
        <div class="mt-5 animate__animated animate__fadeInUp animate__delay-2s">
            <h2 class="text-2xl font-semibold mb-3 text-primary">
                <i class="fas fa-history me-2"></i> My Trip History
            </h2>
            
            <div class="table-responsive">
                <table class="table table-hover border">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Flight</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mar 15, 2025</td>
                            <td>JFK</td>
                            <td>LAX</td>
                            <td>FL1234</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a></td>
                        </tr>
                        <tr>
                            <td>Apr 10, 2025</td>
                            <td>SFO</td>
                            <td>ORD</td>
                            <td>FL5678</td>
                            <td><span class="badge bg-warning text-dark">Upcoming</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a></td>
                        </tr>
                        <tr>
                            <td>Jan 23, 2025</td>
                            <td>DFW</td>
                            <td>MIA</td>
                            <td>FL9012</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<style>
    :root {
        /* Modern color palette - Same as header.php */
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
    }

    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
        border-color: var(--primary-color) !important;
        transform: translateY(-2px);
    }
    
    .focus-ring:focus {
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
        outline: none;
    }

    /* Label styling */
    .form-label {
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }
    
    .form-label:hover {
        transform: translateX(3px);
    }
    
    /* Input group styling */
    .input-group-text {
        border-top-left-radius: 0.5rem;
        border-bottom-left-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    
    .input-group:focus-within {
        transform: scale(1.01);
        transition: transform 0.3s ease;
    }

    /* Update profile button styling */
    .update-profile-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        padding: 0.75rem 2.5rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2), 0 2px 4px -1px rgba(14, 165, 233, 0.1);
    }
    
    .update-profile-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3), 0 4px 6px -2px rgba(14, 165, 233, 0.15);
        transform: translateY(-3px) scale(1.03);
        background: linear-gradient(135deg, var(--primary-dark), var(--accent-color));
        color: white;
    }
    
    .update-profile-btn:active {
        transform: translateY(1px);
    }

    /* Badge styling */
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    
    .bg-primary {
        background-color: var(--primary-color) !important;
    }
    
    .bg-accent {
        background-color: var(--accent-color) !important;
    }
    
    .bg-highlight {
        background-color: var(--highlight) !important;
    }
    
    /* Table styling */
    .table {
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .table th {
        font-weight: 600;
        color: var(--primary-dark);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(14, 165, 233, 0.05);
    }

    /* Animation timing */
    .animate__delay-1s {
        animation-delay: 0.3s;
    }
    
    .animate__delay-2s {
        animation-delay: 0.5s;
    }
    
    .animate__delay-3s {
        animation-delay: 0.7s;
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
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card {
            margin: 1rem;
            width: auto;
        }
        
        .form-control,
        .input-group-text {
            font-size: 0.95rem;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add subtle hover effects to input fields
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('animate__animated', 'animate__pulse');
        });
        
        control.addEventListener('blur', function() {
            this.parentElement.classList.remove('animate__animated', 'animate__pulse');
        });
    });
});
</script>