

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for date inputs
    const datePickers = document.querySelectorAll('.datepicker');
    if (typeof flatpickr !== 'undefined' && datePickers.length > 0) {
        datePickers.forEach(picker => {
            flatpickr(picker, {
                altInput: true,
                altFormat: "F j, Y", // Human readable format
                dateFormat: "Y-m-d", // Format sent to server
                minDate: "today"     // Cannot select past dates
            });
        });
        console.log('Flatpickr initialized.');
    } else if (datePickers.length > 0) {
         console.warn('Flatpickr library not found, but datepicker elements exist.');
    }

    // Add simple confirmation for booking cancellation or important actions if needed
    const confirmButtons = document.querySelectorAll('.confirm-action');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const message = button.getAttribute('data-confirm-message') || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault(); // Stop the action (e.g., form submission)
            }
        });
    });

    // Simple fade out for alerts after a delay
    const autoDismissAlerts = document.querySelectorAll('.alert-auto-dismiss');
    autoDismissAlerts.forEach(alert => {
        setTimeout(() => {
            if (bootstrap && bootstrap.Alert) {
                 const bsAlert = new bootstrap.Alert(alert);
                 bsAlert.close();
            } else {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500); // Remove after fade
            }
        }, 5000); // 5 seconds delay
    });

    console.log('main.js loaded');
});