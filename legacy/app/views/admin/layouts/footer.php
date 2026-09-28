                </div> <!-- container-fluid -->
            </div> <!-- app-content -->
        </main> <!-- app-main -->
        
        <!-- Footer -->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">System Version 4.0</div>
            <strong>Copyright &copy; 2026 <a href="<?= URL_ROOT ?>" class="text-decoration-none">TimNhaDat.site</a>.</strong> All rights reserved.
        </footer>
    </div> <!-- app-wrapper -->
    
    <!-- Required Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="<?= URL_ROOT ?>/public/js/adminlte.min.js"></script>
    <script src="<?= URL_ROOT ?>/public/js/admin-dialog.js?v=1"></script>
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }

        const liveChatBadge = document.querySelector('[data-live-chat-waiting-badge]');
        function updateLiveChatBadge() {
          if (!liveChatBadge) return;
          fetch('<?= URL_ROOT ?>/admin/live-chat/stats', { headers: { 'Accept': 'application/json' } })
            .then(response => response.json())
            .then(data => {
              if (!data.success || !data.stats) return;
              const waiting = Number(data.stats.waiting_for_admin || 0);
              liveChatBadge.textContent = waiting;
              liveChatBadge.classList.toggle('d-none', waiting <= 0);
            })
            .catch(() => {});
        }
        updateLiveChatBadge();
        setInterval(updateLiveChatBadge, 5000);
      });
    </script>
</body>
</html>
