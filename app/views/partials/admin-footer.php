    </main>
</div>

<script>
(function () {
    const toggle = document.querySelector('.admin-nav-toggle');
    const drawer = document.querySelector('.admin-mobile-drawer');
    const backdrop = document.querySelector('.admin-mobile-backdrop');
    const closeButtons = document.querySelectorAll('[data-admin-nav-close]');

    if (!toggle || !drawer || !backdrop) {
        return;
    }

    function openMenu() {
        drawer.hidden = false;
        backdrop.hidden = false;
        document.body.classList.add('admin-nav-open');
        toggle.setAttribute('aria-expanded', 'true');
    }

    function closeMenu() {
        document.body.classList.remove('admin-nav-open');
        toggle.setAttribute('aria-expanded', 'false');

        window.setTimeout(function () {
            if (!document.body.classList.contains('admin-nav-open')) {
                drawer.hidden = true;
                backdrop.hidden = true;
            }
        }, 220);
    }

    toggle.addEventListener('click', function () {
        if (document.body.classList.contains('admin-nav-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    closeButtons.forEach(function (button) {
        button.addEventListener('click', closeMenu);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
})();
</script>
</body>
</html>
