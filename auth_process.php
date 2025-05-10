
<?php
require_once 'db.php';
require_once 'functions.php'; // Starts session

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php'); // Only allow POST requests
}

$action = $_POST['action'] ?? '';

// --- LOGIN ACTION ---
if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $redirect_url = trim($_POST['redirect_url'] ?? 'index.php'); // Get redirect URL

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = 'Email and password are required.';
        redirect('login.php?redirect_url='.urlencode($redirect_url));
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email, password, full_name, role FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_fullname'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            setFlashMessage('success', 'Welcome back, ' . sanitize($user['full_name']) . '!');
            redirect($redirect_url); // Redirect to intended page or index

        } else {
            // Login failed
            $_SESSION['login_error'] = 'Invalid email or password.';
             redirect('login.php?redirect_url='.urlencode($redirect_url));
        }

    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Database error during login. ' . $e->getMessage(); // Show error for debug
        redirect('login.php?redirect_url='.urlencode($redirect_url));
    }
}

// --- REGISTRATION ACTION ---
elseif ($action === 'register') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['register_error'] = 'All fields are required.';
        redirect('register.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Invalid email format.';
        redirect('register.php');
    }
    if (strlen($password) < 6) {
         $_SESSION['register_error'] = 'Password must be at least 6 characters long.';
        redirect('register.php');
    }
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = 'Passwords do not match.';
        redirect('register.php');
    }

    // Check if email already exists
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->fetch()) {
            $_SESSION['register_error'] = 'Email address already registered.';
            redirect('register.php');
        }

        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use default algorithm

        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (:name, :email, :pass, 'customer')");
        $stmt->bindParam(':name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $hashed_password);
        $stmt->execute();

        // Registration successful
        setFlashMessage('success', 'Registration successful! Please log in.');
        redirect('login.php');

    } catch (PDOException $e) {
         $_SESSION['register_error'] = 'Database error during registration: ' . $e->getMessage(); // Debug
         redirect('register.php');
    }
}

// If action is invalid or missing
else {
    redirect('index.php');
}
?>