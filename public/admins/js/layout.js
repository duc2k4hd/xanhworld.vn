document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleIcon = document.getElementById('toggleIcon');
    // Check local storage or default to false
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
        if(toggleIcon) toggleIcon.textContent = '☰';
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
        if(toggleIcon) toggleIcon.textContent = '✕';
    }
    
    // Initialize mobile sidebar state
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('open');
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleIcon = document.getElementById('toggleIcon');
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: toggle open class
        sidebar.classList.toggle('open');
    } else {
        // Desktop: toggle collapsed class
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        if (isCollapsed) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            toggleIcon.textContent = '✕';
            localStorage.setItem('sidebarCollapsed', 'false');
        } else {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('sidebar-collapsed');
            toggleIcon.textContent = '☰';
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
        if (sidebar && !sidebar.contains(e.target) && menuToggle && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// Menu accordion functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuGroups = document.querySelectorAll('.menu-group-header');
    
    menuGroups.forEach(header => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            const groupId = this.getAttribute('data-group');
            const items = document.getElementById(groupId + '-group');
            // const isExpanded = items.classList.contains('expanded'); // Not needed if we toggle logic clearly
            const isCurrentlyExpanded = this.classList.contains('expanded');

            // Close all other groups
            document.querySelectorAll('.menu-group-items').forEach(item => {
                item.classList.remove('expanded');
            });
            document.querySelectorAll('.menu-group-header').forEach(h => {
                h.classList.remove('expanded');
            });
            
            // Toggle current group
            if (!isCurrentlyExpanded) {
                if(items) items.classList.add('expanded');
                this.classList.add('expanded');
            }
        });
    });

    // Auto-expand groups with active items
    document.querySelectorAll('.menu-item.active').forEach(activeItem => {
        const group = activeItem.closest('.menu-group-items');
        if (group) {
            group.classList.add('expanded');
            const header = group.previousElementSibling;
            if (header) {
                header.classList.add('expanded');
            }
        }
    });

    // Back to Top Logic
    const backToTopButton = document.getElementById('backToTop');
    
    if (backToTopButton) {
        // Show/hide button based on scroll position
        function toggleBackToTop() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        }

        // Scroll to top smoothly
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Event listeners
        window.addEventListener('scroll', toggleBackToTop);
        backToTopButton.addEventListener('click', scrollToTop);

        // Initial check
        toggleBackToTop();
    }
});
