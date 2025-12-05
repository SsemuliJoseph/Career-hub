<?php
/**
 * includes/footer.php - Reusable footer component
 *
 * Teaching notes: This is a centralized HTML footer used across all pages via include.
 * Benefits of componentization:
 * - Single source of truth: update once, changes everywhere
 * - Consistent branding and copyright year (dynamic via date('Y'))
 * - Reduces code duplication (DRY principle: Don't Repeat Yourself)
 * - Easier maintenance and updates
 *
 * Usage in pages:
 * <?php include_once __DIR__ . '/../includes/footer.php'; ?>
 * or
 * <?php require_once __DIR__ . '/../includes/footer.php'; ?>
 *
 * Key PHP functions:
 * - date('Y'): returns current 4-digit year (e.g., 2024)
 * - <?= ... ?>: short echo tag equivalent to <?php echo ... ?>
 *
 * HTML attributes explained:
 * - role="contentinfo": ARIA landmark for accessibility (screen readers)
 * - target="_blank": opens link in new tab/window
 * - aria-label: provides accessible label for screen readers
 * - class="social-link": CSS hook for styling
 *
 * SVG icons: Inline SVG for social media icons (Instagram, GitHub, LinkedIn)
 * - xmlns: XML namespace for SVG
 * - viewBox: coordinate system for scaling
 * - stroke/fill: coloring properties (currentColor inherits from CSS color)
 */
?>
<footer class="footer" role="contentinfo">
  <div class="page-content-wrapper footer-content">
    <!-- 
      Dynamic copyright year:
      - date('Y') returns current year as string
      - Updates automatically without code changes
      - Short echo tag (equals sign) is equivalent to full echo statement
    -->
    <p>© <?= date('Y') ?> CaReeR CoNNect HuB. All rights reserved.</p>
    
    <!--
      Social links section:
      - aria-label provides accessible description for screen readers
      - target="_blank" opens in new tab (consider adding rel="noopener noreferrer" for security)
    -->
    <div class="footer-social" aria-label="Social links">
      <span> For more info </span>
      
      <!-- Instagram Link -->
      <a href="https://www.instagram.com/my.preciouspabz" target="_blank" aria-label="Instagram" class="social-link">
        <!-- Instagram Icon (SVG inline for performance and control) -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
          <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
          <circle cx="17.5" cy="6.5" r="1.5"/>
        </svg>
      </a>
      
      <!-- GitHub Link -->
      <a href="https://github.com/pabz123" target="_blank" aria-label="GitHub" class="social-link">
        <!-- GitHub Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 19c-4.5 1.5-4.5-2.5-6-3m12 5v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 18 2.77 5.07 5.07 0 0 0 17.91 0S16.73.35 14 2.48a13.38 13.38 0 0 0-8 0C3.27.35 2.09 0 2.09 0A5.07 5.07 0 0 0 2 2.77 5.44 5.44 0 0 0 .5 8.5c0 5.42 3.3 6.61 6.44 7a3.37 3.37 0 0 0-.94 2.61V21"/>
        </svg>
      </a>
      
      <!-- LinkedIn Link -->
      <a href="https://www.linkedin.com/in/pabz" target="_blank" aria-label="LinkedIn" class="social-link">
        <!-- LinkedIn Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2h-1v9h-4v-9h-4v9H3V9h4v1.2a4.6 4.6 0 0 1 4-2.2z"/>
          <rect x="2" y="9" width="4" height="12"/>
          <circle cx="4" cy="4" r="2"/>
        </svg>
      </a>
    </div>
  </div>
</footer>
