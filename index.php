<?php
$pageTitle = "Search Flights";
$pdo = require_once 'db.php';
require_once 'header.php';
?>

<?php
// Fetch airports for dropdowns/datalist
try {
    $stmt = $pdo->query("SELECT code, name, city FROM airports ORDER BY city, name");
    $airports = $stmt->fetchAll();
} catch (PDOException $e) {
    $airports = []; // Handle error gracefully
    echo "<div class='alert alert-danger'>Could not load airport list.</div>";
}
?>

<div class="card shadow-lg border-0 rounded-lg overflow-hidden max-w-3xl mx-auto animate__animated animate__fadeIn animate__delay-1s">
    <div class="card-header p-4 animate__animated animate__slideInDown" style="background: linear-gradient(135deg, var(--primary-color), var(--accent-color))">
        <h1 class="text-3xl font-semibold mb-0 text-white d-flex align-items-center">
            <i class="fas fa-plane-departure me-3 animate__animated animate__pulse animate__slow animate__infinite"></i> Find Your Next Adventure
        </h1>
    </div>
    <div class="card-body p-4 md:p-5 bg-gradient-to-b from-gray-50 to-white">
        <form action="search_results.php" method="GET" class="space-y-4">
            <div class="row g-3">
                <div class="col-md-6 animate__animated animate__fadeInLeft animate__delay-1s">
                    <label for="origin" class="form-label fw-semibold text-primary">From</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color: rgba(14, 165, 233, 0.15); color: var(--primary-color); border-color: rgba(14, 165, 233, 0.3);">
                            <i class="fas fa-map-marker-alt"></i>
                        </span>
                        <input list="origin-list" class="form-control form-control-lg focus-ring" id="origin" name="origin" placeholder="City or Airport Code" required
                               style="border-color: rgba(14, 165, 233, 0.3);">
                    </div>
                    <datalist id="origin-list">
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?php echo sanitize($airport['code']); ?>"><?php echo sanitize($airport['city']) . ' (' . sanitize($airport['name']) . ')'; ?></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-6 animate__animated animate__fadeInRight animate__delay-1s">
                    <label for="destination" class="form-label fw-semibold" style="color: var(--accent-color);">To</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color: rgba(20, 184, 166, 0.15); color: var(--accent-color); border-color: rgba(20, 184, 166, 0.3);">
                            <i class="fas fa-map-marked-alt"></i>
                        </span>
                        <input list="destination-list" class="form-control form-control-lg focus-ring" id="destination" name="destination" placeholder="City or Airport Code" required
                               style="border-color: rgba(20, 184, 166, 0.3);">
                    </div>
                    <datalist id="destination-list">
                        <?php foreach ($airports as $airport): ?>
                            <option value="<?php echo sanitize($airport['code']); ?>"><?php echo sanitize($airport['city']) . ' (' . sanitize($airport['name']) . ')'; ?></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6 animate__animated animate__fadeInUp animate__delay-2s">
                    <label for="departure_date" class="form-label fw-semibold" style="color: var(--primary-dark);">Depart Date</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color: rgba(2, 132, 199, 0.15); color: var(--primary-dark); border-color: rgba(2, 132, 199, 0.3);">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <input type="date" class="form-control form-control-lg focus-ring" name="departure_date" id="departure_date" placeholder="Select Date" required autocomplete="off"
                               style="border-color: rgba(2, 132, 199, 0.3);">
                    </div>
                </div>
                <div class="col-md-6 animate__animated animate__fadeInUp animate__delay-2s">
                    <label for="passengers" class="form-label fw-semibold" style="color: var(--primary-light);">Passengers</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background-color: rgba(56, 189, 248, 0.15); color: var(--primary-light); border-color: rgba(56, 189, 248, 0.3);">
                            <i class="fas fa-users"></i>
                        </span>
                        <input type="number" class="form-control form-control-lg focus-ring" id="passengers" name="passengers" min="1" max="9" value="1" required
                               style="border-color: rgba(56, 189, 248, 0.3);">
                    </div>
                </div>
            </div>

            <div class="text-center pt-4 animate__animated animate__fadeInUp animate__delay-3s">
                <button type="submit" class="btn btn-lg px-5 py-3 search-flight-btn">
                    <i class="fas fa-search me-2"></i> Find Flights
                </button>
            </div>
        </form>
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

    /* Search button styling */
    .search-flight-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        padding: 0.75rem 2.5rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2), 0 2px 4px -1px rgba(14, 165, 233, 0.1);
    }
    
    .search-flight-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3), 0 4px 6px -2px rgba(14, 165, 233, 0.15);
        transform: translateY(-3px) scale(1.03);
        background: linear-gradient(135deg, var(--primary-dark), var(--accent-color));
        color: white;
    }
    
    .search-flight-btn:active {
        transform: translateY(1px);
    }

    /* Date picker customization */
    input[type="date"] {
        color: #334155;
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
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('departure_date').min = today;
    
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