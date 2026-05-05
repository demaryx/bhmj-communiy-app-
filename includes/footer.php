    </main>
    
    <footer style="margin-left: var(--sidebar-width); padding: 30px; border-top: 1px solid var(--border); background: #fff; text-align: center; transition: margin-left 0.3s;">
        <div style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">
            &copy; <?= date('Y') ?> BHMJ Membership Portal. All Rights Reserved.
        </div>
        <div style="margin-top: 8px; font-size: 0.85rem; color: var(--secondary);">
            Design & Development by <a href="https://demaryx.com" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 800;">DEMARYX</a>
        </div>
    </footer>

    <script>
        // Smooth transitions and mobile menu handling
        document.addEventListener('DOMContentLoaded', function() {
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                const sidebar = document.getElementById('sidebar');
                const menuToggle = document.querySelector('.menu-toggle');
                if (sidebar && menuToggle && window.innerWidth <= 1024) {
                    if (!sidebar.contains(event.target) && 
                        !menuToggle.contains(event.target) && 
                        sidebar.classList.contains('open')) {
                        sidebar.classList.remove('open');
                    }
                }
            });
        });

        // Add CSS to handle responsive footer
        const style = document.createElement('style');
        style.innerHTML = `
            @media (max-width: 1024px) {
                footer { margin-left: 0 !important; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
