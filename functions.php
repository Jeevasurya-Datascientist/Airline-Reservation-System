
<?php
// Start session if not already started - MUST be before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ***** ADD THIS LINE *****
define('CURRENCY_SYMBOL', '₹');
// *************************

/**
 * Basic output sanitization. ALWAYS use this before echoing user-provided data or DB data.
 * @param string|null $data
 * @return string
 */
function sanitize(?string $data): string {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Check if a user is logged in.
 * @return bool
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin.
 * @return bool
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a given path within the project.
 * @param string $path Relative path (e.g., 'login.php', 'index.php')
 */
function redirect(string $path): void {
    // Basic redirect assuming files are in the root project directory
    header("Location: $path");
    exit; // Stop script execution after redirect
}

/**
 * Generate a simple booking reference (Not guaranteed unique in high concurrency).
 * @param int $length
 * @return string
 */
function generateSimpleBookingRef(int $length = 6): string {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $ref = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $ref .= $characters[random_int(0, $max)];
    }
    // IMPORTANT: In a real system, you MUST check if this ref exists in DB and loop if needed.
    // For exam simplicity, we skip this check.
    return $ref;
}

/**
 * Set a flash message in the session.
 * @param string $type 'success' or 'error'
 * @param string $message
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display and clear the flash message.
 */
function displayFlashMessage(): void {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; // Map to Bootstrap alert types
        $message = sanitize($_SESSION['flash_message']['message']);
        echo "<div class='container mt-3'><div class='alert alert-{$type} alert-dismissible fade show alert-auto-dismiss' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div></div>";
        unset($_SESSION['flash_message']); // Clear after displaying
    }
}
?>