/**
 * js/scripts.js - General UI Utility Functions
 * Teaching: This file contains small helper functions for common UI patterns.
 * - It uses DOMContentLoaded event to ensure DOM is ready before running code.
 * - querySelector/querySelectorAll are modern DOM APIs (replace older getElementById).
 * - arrow functions (=>) are concise function syntax (ES6).
 * - forEach is an array method that executes a function for each element.
 * 
 * Key patterns demonstrated:
 * - Event delegation: attach listener to parent, check target in handler.
 * - classList.toggle(): adds class if not present, removes if present.
 * - Auto-resizing textarea: adjust height based on scrollHeight.
 */

// DOMContentLoaded fires when HTML is parsed (images/stylesheets may still be loading)
document.addEventListener('DOMContentLoaded', function(){
  // Mobile nav toggle (hamburger menu)
  // Teaching: querySelector returns the first element matching the selector (or null).
  const tgl = document.querySelector('.mobile-toggle');
  const nav = document.querySelector('.topnav');
  
  // Check if elements exist before adding event listener (defensive programming)
  if(tgl && nav){
    // addEventListener attaches a click handler to the toggle button
    tgl.addEventListener('click', function(){
      // classList.toggle('class') adds the class if not present, removes if present
      nav.classList.toggle('open');
    });
  }

  // Auto-resize textareas marked with data-autoresize attribute
  // Teaching: querySelectorAll returns a NodeList of all matching elements.
  // We use forEach to iterate over each textarea and set up auto-resize.
  document.querySelectorAll('textarea[data-autoresize]').forEach(t => {
    // overflow: hidden prevents scrollbars (we adjust height instead)
    t.style.overflow = 'hidden';
    
    // Teaching: arrow function stored in variable for reusability
    const resize = () => { 
      t.style.height = 'auto';  // Reset height to auto first
      // scrollHeight is the full height of the content (including hidden overflow)
      t.style.height = (t.scrollHeight) + 'px';  // Set height to fit content
    };
    
    // Listen for input events (fires on every keystroke/paste)
    t.addEventListener('input', resize);
    
    // Call resize once on load to set initial height
    resize();
  });
});
