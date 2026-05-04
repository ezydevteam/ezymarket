/**
 * ============================================
 * USER PANEL SIDEBAR COLLAPSE HANDLER
 * ============================================
 * Handles icon-only sidebar with hover expansion
 * Desktop only - Mobile keeps default slide behavior
 */

(function () {
  'use strict';

  // Desktop check
  function isDesktop() {
    return window.innerWidth >= 1200;
  }

  // Get elements
  const sidebar = document.querySelector('.dashboard-sidebar');
  const toggleBtn = document.querySelector('.sidebar-toggle-btn');
  const mobileToggleBtn = document.querySelector('.dashboard-toggle-btn');
  const dashboardBody = document.querySelector('.dashboard-body');

  if (!sidebar) return;

  // LocalStorage key
  const STORAGE_KEY = 'user_sidebar_collapsed';

  /**
   * Update toggle button icon
   */
  function updateToggleIcon() {
    if (!toggleBtn) return;

    const icon = toggleBtn.querySelector('i');
    if (!icon) return;

    if (sidebar.classList.contains('collapsed')) {
      // Empty circle when collapsed
      icon.className = 'bi bi-circle';
    } else {
      // Filled circle when expanded
      icon.className = 'bi bi-record-circle';
    }
  }

  /**
   * Update aria-expanded attribute
   */
  function updateAriaExpanded() {
    if (!toggleBtn) return;

    const isCollapsed = sidebar.classList.contains('collapsed');
    toggleBtn.setAttribute('aria-expanded', !isCollapsed);
  }

  /**
   * Toggle sidebar collapse state
   */
  function toggleSidebar() {
    if (!isDesktop()) return;

    sidebar.classList.toggle('collapsed');

    // Save state to localStorage
    const isCollapsed = sidebar.classList.contains('collapsed');
    localStorage.setItem(STORAGE_KEY, isCollapsed ? 'true' : 'false');

    updateToggleIcon();
    updateAriaExpanded();
  }

  /**
   * Load saved state from localStorage
   */
  function loadSavedState() {
    if (!isDesktop()) {
      sidebar.classList.remove('collapsed');
      return;
    }

    const savedState = localStorage.getItem(STORAGE_KEY);
    if (savedState === 'true') {
      sidebar.classList.add('collapsed');
    } else {
      sidebar.classList.remove('collapsed');
    }

    updateToggleIcon();
    updateAriaExpanded();
  }

  /**
   * Handle window resize
   */
  function handleResize() {
    if (!isDesktop()) {
      // Remove collapsed state on mobile
      sidebar.classList.remove('collapsed');
      if (toggleBtn) {
        toggleBtn.style.display = 'none';
      }
    } else {
      // Restore saved state on desktop
      loadSavedState();
      if (toggleBtn) {
        toggleBtn.style.display = '';
      }
    }
  }

  // Event Listeners
  if (toggleBtn) {
    toggleBtn.addEventListener('click', toggleSidebar);
    toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
    toggleBtn.setAttribute('aria-expanded', 'true');
  }

  // Mobile toggle button should work independently
  if (mobileToggleBtn) {
    mobileToggleBtn.addEventListener('click', function() {
      document.querySelector('.dashboard').classList.toggle('toggle');
    });
  }

  // Window resize handler with debounce
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(handleResize, 250);
  });

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', function() {
    loadSavedState();
  });

  // Load immediately if DOM is already ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadSavedState);
  } else {
    loadSavedState();
  }

  // Prevent hover expansion from affecting dropdown menus
  const dropdownLinks = document.querySelectorAll('.dashboard-sidebar .dashboard-toggle');
  dropdownLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      if (isDesktop() && sidebar.classList.contains('collapsed')) {
        // Don't prevent default - allow dropdown to work
        // But keep sidebar in collapsed state after click
        setTimeout(() => {
          if (!sidebar.matches(':hover')) {
            sidebar.classList.add('collapsed');
          }
        }, 100);
      }
    });
  });

})();
