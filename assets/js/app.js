/* ============================================================
   ANIKOTO – Main JavaScript
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Sidebar Toggle ----------
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    if (sidebarToggle && sidebar) {
        function toggleSidebar(show) {
            if (show === undefined) {
                sidebar.classList.toggle('open');
                if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
            } else if (show) {
                sidebar.classList.add('open');
                if (sidebarOverlay) sidebarOverlay.classList.add('show');
            } else {
                sidebar.classList.remove('open');
                if (sidebarOverlay) sidebarOverlay.classList.remove('show');
            }
        }
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleSidebar();
        });
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                toggleSidebar(false);
            });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                toggleSidebar(false);
            }
        });
    }

    // ---------- Hero Slider ----------
    const slider = document.getElementById('heroSlider');
    if (slider) {
        const slides = slider.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        let current = 0;
        let interval;

        function goTo(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            current = index;
        }

        dots.forEach(dot => {
            dot.addEventListener('click', function () {
                goTo(parseInt(this.dataset.index));
                resetInterval();
            });
        });

        function resetInterval() {
            clearInterval(interval);
            interval = setInterval(() => goTo(current + 1), 6000);
        }

        if (slides.length > 1) resetInterval();
    }

    // ---------- Search AJAX ----------
    const searchInput = document.getElementById('searchInput');
    const suggestions = document.getElementById('searchSuggestions');
    const clearBtn = document.getElementById('searchClear');
    let searchTimeout;

    if (searchInput && suggestions) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim();
            clearBtn.style.display = q ? 'block' : 'none';

            clearTimeout(searchTimeout);
            if (q.length < 2) { suggestions.classList.remove('show'); return; }

            searchTimeout = setTimeout(() => {
                fetch(BASE_URL + '/search/ajax?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (data.html) {
                            suggestions.innerHTML = data.html;
                            suggestions.classList.add('show');
                        } else {
                            suggestions.classList.remove('show');
                        }
                    }).catch(() => {});
            }, 300);
        });

        searchInput.addEventListener('blur', function () {
            setTimeout(() => suggestions.classList.remove('show'), 200);
        });

        searchInput.addEventListener('focus', function () {
            if (this.value.trim().length >= 2) suggestions.classList.add('show');
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                suggestions.classList.remove('show');
                this.style.display = 'none';
                searchInput.focus();
            });
        }
    }

    // ---------- Modal System ----------
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const resetModal = document.getElementById('resetModal');

    const loginClose = document.getElementById('loginModalClose');
    const registerClose = document.getElementById('registerModalClose');
    const resetClose = document.getElementById('resetModalClose');

    const registerLink = document.getElementById('registerLink');
    const loginLink = document.getElementById('loginLink');
    const loginLink2 = document.getElementById('loginLink2');
    const forgotLink = document.getElementById('forgotLink');

    function showModal(modal) {
        [loginModal, registerModal, resetModal].forEach(m => { if (m) m.classList.remove('show'); });
        if (modal) modal.classList.add('show');
    }

    function hideModals() {
        [loginModal, registerModal, resetModal].forEach(m => { if (m) m.classList.remove('show'); });
    }

    if (loginBtn && loginModal) loginBtn.addEventListener('click', () => showModal(loginModal));
    if (loginClose) loginClose.addEventListener('click', hideModals);
    if (registerClose) registerClose.addEventListener('click', hideModals);
    if (resetClose) resetClose.addEventListener('click', hideModals);
    if (registerLink && registerModal) registerLink.addEventListener('click', () => showModal(registerModal));
    if (loginLink && loginModal) loginLink.addEventListener('click', () => showModal(loginModal));
    if (loginLink2 && loginModal) loginLink2.addEventListener('click', () => showModal(loginModal));
    if (forgotLink && resetModal) forgotLink.addEventListener('click', () => showModal(resetModal));

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-overlay')) hideModals();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideModals();
    });

    // ---------- User Dropdown ----------
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');
    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function () {
            userDropdown.classList.remove('show');
        });
    }
});


