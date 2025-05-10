
<?php
$pageTitle = "Login";
require_once 'functions.php'; // Starts session

// If already logged in, redirect to home or dashboard
if (isLoggedIn()) {
    redirect('index.php');
}

$redirect_url = trim($_GET['redirect_url'] ?? 'index.php'); // Where to go after login

require_once 'header.php';
?>
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
        background-image: linear-gradient(135deg, var(--primary-color), var(--accent-color), var(--primary-dark));
        color: white;
        padding: 1.5rem;
        border-top-left-radius: 0.5rem !important;
        border-top-right-radius: 0.5rem !important;
    }
    
    /* Login specific styles */
    .login-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        padding: 0.75rem 2.5rem;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2), 0 2px 4px -1px rgba(14, 165, 233, 0.1);
        height: calc(3rem + 2px);
    }
    
    .login-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3), 0 4px 6px -2px rgba(14, 165, 233, 0.15);
        transform: translateY(-3px) scale(1.03);
        background: linear-gradient(135deg, var(--primary-dark), var(--accent-color));
        color: white;
    }
    
    .login-btn:active {
        transform: translateY(1px);
    }
    
    .login-link {
        color: var(--primary-color);
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .login-link:hover {
        color: var(--primary-dark);
        text-decoration: none;
        transform: translateX(3px);
        display: inline-block;
    }
    
    .alert-danger {
        background-color: rgba(254, 226, 226, 0.9);
        border-color: rgba(248, 113, 113, 0.3);
        color: #b91c1c;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
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
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-indigo-600 text-white">
                <h1 class="text-2xl mb-0 font-semibold">Login</h1>
            </div>
            <div class="card-body">
                 <?php
                    // Display login errors if redirected back
                    if (isset($_SESSION['login_error'])) {
                        echo '<div class="alert alert-danger">' . sanitize($_SESSION['login_error']) . '</div>';
                        unset($_SESSION['login_error']);
                    }
                 ?>

                <form action="auth_process.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="redirect_url" value="<?php echo sanitize($redirect_url); ?>"> <?php // Pass redirect URL ?>

                    <div class="mb-4 animate__animated animate__fadeInUp animate__delay-1s">
    <label for="email" class="form-label">Email address</label>
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-envelope text-primary-light"></i>
        </span>
        <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="your@email.com">
    </div>
</div>

<div class="mb-4 animate__animated animate__fadeInUp animate__delay-2s">
    <label for="password" class="form-label">Password</label>
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
            <i class="fas fa-lock text-primary-light"></i>
        </span>
        <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
        <button type="button" class="btn btn-outline-secondary border border-start-0" onclick="togglePassword()">
            <i class="fas fa-eye" id="toggleIcon"></i>
        </button>
    </div>
</div>

<div class="mb-4 form-check animate__animated animate__fadeInUp animate__delay-2s">
    <input type="checkbox" class="form-check-input focus-ring" id="remember" name="remember">
    <label class="form-check-label" for="remember">Remember me</label>
</div>


<div class="mt-4 animate__animated animate__fadeInUp animate__delay-3s">
    <button type="submit" class="login-btn w-100 d-flex align-items-center justify-content-center">
        <span>Sign In</span>
        <i class="fas fa-arrow-right ms-2"></i>
    </button>
</div>
</form>

<div class="mt-4 text-center animate__animated animate__fadeIn animate__delay-3s">
    <a href="forgot-password.php" class="login-link">Forgot your password?</a>
</div>

<div class="mt-4 pt-3 border-t border-gray-200 animate__animated animate__fadeIn animate__delay-3s">
    <p class="text-center">
        Don't have an account? <a href="register.php" class="login-link">Register here</a>
    </p>
</div>
</div>
</div>
</div>
</div>

<?php require_once 'footer.php'; ?>