/**
 * TimNhaDat.site - Premium Page Loader & Transitions Script
 * Controls page-load intro animations, page exit transitions, and fallback behaviors.
 */
(function() {
    function initLoader() {
        const loader = document.getElementById('page-loader');
        const progressBar = loader ? loader.querySelector('.loader-progress-bar') : null;
        const statusText = loader ? loader.querySelector('.loader-status') : null;
        
        if (!loader) return;

        // Phase 1: Simulate initial loading progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            if (progress < 85) {
                // Accelerate progress initially, then slow down as it gets closer to 90%
                const increment = progress < 50 ? Math.floor(Math.random() * 15) + 5 : Math.floor(Math.random() * 5) + 1;
                progress = Math.min(progress + increment, 85);
                if (progressBar) progressBar.style.width = progress + '%';
            }
        }, 100);

        // Phase 2: Page completely loaded (Images, stylesheets, frames, etc.)
        function completeLoader() {
            clearInterval(progressInterval);
            if (progressBar) {
                progressBar.style.transition = 'width 0.2s ease-out';
                progressBar.style.width = '100%';
            }
            if (statusText) statusText.textContent = 'Hoàn tất kết nối!';

            // Smooth transition out after full width progress
            setTimeout(() => {
                loader.classList.add('fade-out');
            }, 250);
        }

        // Bind completion to window load event
        if (document.readyState === 'complete') {
            completeLoader();
        } else {
            window.addEventListener('load', completeLoader);
        }

        // Phase 3: Safe Fallback Timeout
        // If the page loads very slow (heavy assets/network lag), force-hide loader after 2s for optimal UX
        const fallbackTimeout = setTimeout(() => {
            clearInterval(progressInterval);
            completeLoader();
        }, 2000);

        // Phase 4: Smooth Outgoing Page Transitions
        // Intercepts clicks on internal links to fade loader back in before changing location
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            
            // Skip checks if no anchor element clicked
            if (!link) return;

            const href = link.getAttribute('href');
            
            // Skip utility, JavaScript, external or target blank links
            if (!href) return;
            if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) return;
            if (link.getAttribute('target') === '_blank') return;
            
            // Skip links that toggle modals or dropdowns (Bootstrap specific)
            if (link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-toggle')) return;
            
            // Verify hostname to make sure it's an internal link
            const linkUrl = new URL(href, window.location.href);
            if (linkUrl.hostname !== window.location.hostname) return;

            // Skip same-page anchor navigation (e.g. /du-an#section-1)
            if (linkUrl.pathname === window.location.pathname && linkUrl.hash) return;
            
            // Custom check to bypass logout links or specific non-page redirects
            if (href.includes('logout') || href.includes('download')) return;

            // Prevent default browser transition temporarily
            e.preventDefault();
            
            // Clear safety fallback timeout
            clearTimeout(fallbackTimeout);

            // Prep progress bar and slide/fade loader back in
            if (progressBar) {
                progressBar.style.transition = 'none';
                progressBar.style.width = '0%';
                // Force redraw/reflow
                progressBar.offsetHeight;
                progressBar.style.transition = 'width 0.4s cubic-bezier(0.25, 1, 0.5, 1)';
                progressBar.style.width = '35%';
            }
            
            if (statusText) statusText.textContent = 'Đang chuyển trang...';
            loader.classList.remove('fade-out');

            // Smoothly redirect after transition overlay completes fade-in
            setTimeout(() => {
                window.location.href = href;
            }, 250);
        });

        // Phase 5: Browser Back/Forward Caching Fix
        // If the browser navigates back to this page using the history cache,
        // make sure the loader isn't stuck active.
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                loader.classList.add('fade-out');
            }
        });
    }

    // Immediately execute or register DOMContentLoaded based on browser's readyState
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLoader);
    } else {
        initLoader();
    }
})();
