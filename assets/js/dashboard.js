/**
 * Dashboard Setup
 * 
 * Sets up the collapsible sidebar for mobile and general admin layout fixes
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Sidebar collapsible trigger toggle for mobile
    const toggleButton = document.getElementById('cv-menu-toggle');
    const sidebar = document.querySelector('.cv-sidebar');

    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        // Close sidebar if clicking outside of it on mobile
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== toggleButton) {
                sidebar.classList.remove('active');
            }
        });
    }

    // Dismiss Alerts smoothly
    const alertDismissButtons = document.querySelectorAll('.cv-alert-close');
    alertDismissButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const alertBox = this.closest('.cv-alert');
            if (alertBox) {
                alertBox.style.opacity = '0';
                alertBox.style.transition = 'opacity 0.4s ease';
                setTimeout(() => {
                    alertBox.remove();
                }, 400);
            }
        });
    });
});
