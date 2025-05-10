<?php
$pageTitle = "Register";
require_once 'functions.php'; // Starts session

// If already logged in, redirect
if (isLoggedIn()) {
    redirect('index.php');
}

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
        display: inline-block;
    }
    
    .form-label:hover {
        transform: translateX(3px);
    }
    
    /* Input group styling */
    .input-group {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        width: 100%;
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

    /* Button styling */
    .register-btn {
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
    
    .register-btn:hover {
        box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3), 0 4px 6px -2px rgba(14, 165, 233, 0.15);
        transform: translateY(-3px) scale(1.03);
        background: linear-gradient(135deg, var(--primary-dark), var(--accent-color));
        color: white;
    }
    
    .register-btn:active {
        transform: translateY(1px);
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
    
    /* Link styling */
    .auth-link {
        color: var(--primary-color);
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .auth-link:hover {
        color: var(--primary-dark);
        text-decoration: none;
        transform: translateX(3px);
        display: inline-block;
    }
    
    /* Error message styling */
    .alert-danger {
        background-color: rgba(254, 226, 226, 0.9);
        border-color: rgba(248, 113, 113, 0.3);
        color: #b91c1c;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        animation: fadeIn 0.5s ease-in-out;
    }
    
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
    
    /* Password strength indicator */
    .password-strength {
        height: 5px;
        margin-top: 0.5rem;
        border-radius: 5px;
        transition: all 0.3s ease;
        background-color: #e2e8f0;
        overflow: hidden;
    }
    
    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
        border-radius: 5px;
    }
    
    /* Animation keyframes */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Bootstrap-like utility classes that might be missing */
    .container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }
    
    .col-md-7 {
        position: relative;
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
    }
    
    .col-lg-6 {
        position: relative;
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
    }
    
    .justify-content-center {
        justify-content: center !important;
    }
    
    .shadow-lg {
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
    }
    
    .rounded-lg {
        border-radius: 0.5rem !important;
    }
    
    .p-4 {
        padding: 1.5rem !important;
    }
    
    .mb-4 {
        margin-bottom: 1.5rem !important;
    }
    
    .mt-4 {
        margin-top: 1.5rem !important;
    }
    
    .pt-3 {
        padding-top: 1rem !important;
    }
    
    .py-5 {
        padding-top: 3rem !important;
        padding-bottom: 3rem !important;
    }
    
    .text-center {
        text-align: center !important;
    }
    
    .w-100 {
        width: 100% !important;
    }
    
    .d-flex {
        display: flex !important;
    }
    
    .align-items-center {
        align-items: center !important;
    }
    
    .justify-content-center {
        justify-content: center !important;
    }
    
    .ms-2 {
        margin-left: 0.5rem !important;
    }
    
    .text-primary-light {
        color: var(--primary-light) !important;
    }
    
    .bg-white {
        background-color: #fff !important;
    }
    
    .border-end-0 {
        border-right: 0 !important;
    }
    
    .border-start-0 {
        border-left: 0 !important;
    }
    
    .ps-0 {
        padding-left: 0 !important;
    }
    
    .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
    }
    
    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }
    
    .border {
        border: 1px solid #dee2e6 !important;
    }
    
    .border-t {
        border-top: 1px solid;
    }
    
    .border-gray-200 {
        border-color: #e5e7eb;
    }
    
    /* Fix for animate__animated classes */
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
    
    /* Responsive adjustments */
    @media (min-width: 768px) {
        .col-md-7 {
            flex: 0 0 58.333333%;
            max-width: 58.333333%;
        }
    }
    
    @media (min-width: 992px) {
        .col-lg-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
    
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

<div class="container py-5 animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-lg rounded-lg animate__animated animate__fadeInUp">
                <div class="card-header">
                    <h1 class="text-2xl mb-0 font-semibold">Create Account</h1>
                </div>
                <div class="card-body p-4">
                    <?php
                        // Display registration errors if redirected back
                        if (isset($_SESSION['register_error'])) {
                            echo '<div class="alert alert-danger">' . sanitize($_SESSION['register_error']) . '</div>';
                            unset($_SESSION['register_error']);
                        }
                    ?>
                    <form action="auth_process.php" method="POST" id="registerForm">
                        <input type="hidden" name="action" value="register">
                        

                        <div class="mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                            <label for="full_name" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-user text-primary-light"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0" id="full_name" name="full_name" required placeholder="Your full name">
                            </div>
                        </div>
                        
                        <div class="mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                            <label for="email" class="form-label">Email Address</label>
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
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required minlength="6" placeholder="Minimum 6 characters" onkeyup="checkPasswordStrength()">
                                <button type="button" class="btn btn-outline-secondary border border-start-0" onclick="togglePassword('password', 'toggleIcon1')">
                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" id="passwordStrengthBar"></div>
                            </div>
                            <div class="form-text" id="passwordHelpText">Minimum 6 characters required.</div>
                        </div>
                        
                        <div class="mb-4 animate__animated animate__fadeInUp animate__delay-2s">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-lock text-primary-light"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" required placeholder="Re-enter your password" onkeyup="checkPasswordMatch()">
                                <button type="button" class="btn btn-outline-secondary border border-start-0" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                            <div class="form-text" id="confirmPasswordHelpText"></div>
                        </div>

                        <div class="mt-4 animate__animated animate__fadeInUp animate__delay-3s">
                            <button type="submit" id="registerButton" class="register-btn w-100 d-flex align-items-center justify-content-center">
                                <span>Create Account</span>
                                <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 pt-3 border-t border-gray-200 animate__animated animate__fadeIn animate__delay-3s">
                        <p class="text-center">
                            Already have an account? <a href="login.php" class="auth-link">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
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

// Check password strength
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('passwordStrengthBar');
    const helpText = document.getElementById('passwordHelpText');
    
    // Reset
    strengthBar.style.width = '0%';
    strengthBar.style.backgroundColor = '#e2e8f0';
    
    if (password.length === 0) {
        helpText.textContent = 'Minimum 6 characters required.';
        return;
    }
    
    // Calculate strength
    let strength = 0;
    
    // Add points for length
    if (password.length >= 6) strength += 25;
    if (password.length >= 8) strength += 15;
    
    // Add points for complexity
    if (/[A-Z]/.test(password)) strength += 15;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^A-Za-z0-9]/.test(password)) strength += 15;
    
    // Update the strength bar
    strengthBar.style.width = strength + '%';
    
    // Set color and message based on strength
    if (strength < 30) {
        strengthBar.style.backgroundColor = '#ef4444';
        helpText.textContent = 'Weak password';
    } else if (strength < 60) {
        strengthBar.style.backgroundColor = '#f59e0b';
        helpText.textContent = 'Medium strength password';
    } else {
        strengthBar.style.backgroundColor = '#10b981';
        helpText.textContent = 'Strong password';
    }
}

// Check if passwords match
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const helpText = document.getElementById('confirmPasswordHelpText');
    
    if (confirmPassword.length === 0) {
        helpText.textContent = '';
        return;
    }
    
    if (password === confirmPassword) {
        helpText.textContent = 'Passwords match';
        helpText.style.color = '#10b981';
    } else {
        helpText.textContent = 'Passwords do not match';
        helpText.style.color = '#ef4444';
    }
}

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(event) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        event.preventDefault();
        document.getElementById('confirmPasswordHelpText').textContent = 'Passwords do not match';
        document.getElementById('confirmPasswordHelpText').style.color = '#ef4444';
    }
});
</script>

<?php require_once 'footer.php'; ?>