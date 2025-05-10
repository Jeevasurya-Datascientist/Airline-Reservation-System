<?php require_once __DIR__ . '/functions.php'; // Include functions (starts session) ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Airline Booking System'; // Page title variable ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Tailwind CSS (Generated) -->
    <link href="assets/css/tailwind.output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Flatpickr CSS (Date Picker) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Custom Styles -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        :root {
            /* Modern color palette - Sky blue to teal gradient */
            --primary-color: #0ea5e9; /* Sky-500 */
            --primary-dark: #0284c7;  /* Sky-600 */
            --primary-light: #38bdf8; /* Sky-400 */
            --accent-color: #14b8a6;  /* Teal-500 */
            --highlight: #fbbf24;     /* Amber-400 */
            --highlight-light: #fcd34d; /* Amber-300 */
            --dark-bg: #0f172a;       /* Slate-900 */
            --dark-surface: #1e293b;  /* Slate-800 */
            --light-text: #f1f5f9;    /* Slate-100 */
            --muted-text: #94a3b8;    /* Slate-400 */
            --transition-speed: 0.3s;
        }
        
        body {
            background-color: #f8fafc; /* Slate-50 */
        }
        
        /* Modern nav styling with sky to teal gradient */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 0.75rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            color: white;
            transition: transform var(--transition-speed) ease-in-out;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand:hover {
            color: white;
            transform: scale(1.03);
        }
        
        .navbar-brand i {
            color: var(--highlight);
            margin-right: 0.5rem;
            font-size: 1.5rem;
        }
        
        /* Nav links styling */
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            transition: all var(--transition-speed) ease;
            position: relative;
            margin: 0 0.25rem;
        }
        
        .nav-link:hover {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            color: white !important;
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .nav-link i {
            opacity: 0.9;
            margin-right: 0.5rem;
            transition: transform 0.2s ease;
        }
        
        .nav-link:hover i {
            transform: translateY(-1px);
        }
        
        /* Dropdown styling */
        .dropdown-menu {
            background-color: var(--dark-surface);
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 0.5rem;
            margin-top: 0.5rem;
            min-width: 12rem;
        }
        
        .dropdown-item {
            color: var(--light-text);
            border-radius: 0.375rem;
            padding: 0.65rem 1rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .dropdown-item i {
            color: var(--muted-text);
            margin-right: 0.75rem;
            width: 1rem;
            text-align: center;
        }
        
        .dropdown-item:hover i {
            color: var(--highlight);
        }
        
        /* User dropdown toggle */
        .dropdown-toggle {
            display: flex;
            align-items: center;
        }
        
        .dropdown-toggle::after {
            margin-left: 0.5rem;
            transition: transform 0.2s ease;
        }
        
        .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }
        
        /* Navbar toggler */
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.3);
            padding: 0.35rem 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .navbar-nav {
                padding-top: 0.5rem;
            }
            
            .nav-link {
                padding: 0.75rem 1rem;
                margin: 0.25rem 0;
            }
            
            .dropdown-menu {
                background-color: rgba(255, 255, 255, 0.05);
                border: none;
                box-shadow: none;
                padding-left: 1rem;
            }
            
            .dropdown-item {
                padding: 0.75rem 1rem;
            }
        }
        
        /* Flash message styling */
        .alert {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Animation delays */
        .animate-delay-1 {
            animation-delay: 0.1s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-3 {
            animation-delay: 0.3s;
        }
        
        .animate-delay-4 {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark sticky-top animate__animated animate__fadeInDown animate__faster">
    <div class="container">
        <a class="navbar-brand animate__animated animate__fadeInLeft" href="index.php">
            <i class="fas fa-plane-departure animate__animated animate__pulse animate__infinite animate__slow"></i>
            SkyJourney
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item animate__animated animate__fadeInRight animate-delay-1">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                         <i class="fas fa-search"></i> Search Flights
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item animate__animated animate__fadeInRight animate-delay-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'active' : ''; ?>" href="my_bookings.php">
                            <i class="fas fa-briefcase"></i> My Bookings
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item animate__animated animate__fadeInRight animate-delay-3">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_bookings.php' ? 'active' : ''; ?>" href="admin_bookings.php">
                                <i class="fas fa-user-shield"></i> Admin View
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown animate__animated animate__fadeInRight animate-delay-4">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo sanitize($_SESSION['user_fullname'] ?? 'User'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn animate__faster" aria-labelledby="navbarDropdown">
                           
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item animate__animated animate__fadeInRight animate-delay-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>" href="login.php">
                             <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item animate__animated animate__fadeInRight animate-delay-3">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>" href="register.php">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4 flex-grow-1">
<?php displayFlashMessage(); // Display success/error messages ?>