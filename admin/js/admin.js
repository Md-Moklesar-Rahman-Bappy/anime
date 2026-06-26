(function() {
    'use strict';

    // Sidebar toggle
    var sidebar = document.getElementById('adminSidebar');
    var toggleBtn = document.getElementById('sidebarToggle');
    var closeBtn = document.getElementById('sidebarClose');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('open');
        });
    }

    // Close sidebar on outside click (mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('open')) {
            var isSidebar = sidebar.contains(e.target);
            var isToggle = toggleBtn && toggleBtn.contains(e.target);
            if (!isSidebar && !isToggle) {
                sidebar.classList.remove('open');
            }
        }
    });

    // Source tabs in episode editor
    var sourceTabs = document.querySelectorAll('.source-tab');
    sourceTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.getAttribute('data-target');
            var container = this.closest('.source-tabs').parentElement;

            container.querySelectorAll('.source-tab').forEach(function(t) {
                t.classList.remove('active');
            });
            container.querySelectorAll('.source-panel').forEach(function(p) {
                p.classList.remove('active');
            });

            this.classList.add('active');
            var panel = container.querySelector('.source-panel[data-panel="' + target + '"]');
            if (panel) panel.classList.add('active');
        });
    });

    // Auto-dismiss alerts
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            if (alert.parentElement) {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(function() { if (alert.parentElement) alert.remove(); }, 300);
            }
        }, 5000);
    });

    // Confirm deletes
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm') || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // YouTube URL parser (live preview)
    var youtubeInputs = document.querySelectorAll('.youtube-url-input');
    youtubeInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            var preview = this.closest('.source-panel').querySelector('.youtube-preview');
            if (!preview) return;
            var url = this.value.trim();
            var id = '';
            var match = url.match(
                /(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
            );
            if (match) {
                id = match[1];
                preview.innerHTML = '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' + id + '" frameborder="0" allowfullscreen></iframe>';
                preview.style.display = 'block';
            } else if (url.match(/^[a-zA-Z0-9_-]{11}$/)) {
                id = url;
                preview.innerHTML = '<iframe width="100%" height="200" src="https://www.youtube.com/embed/' + id + '" frameborder="0" allowfullscreen></iframe>';
                preview.style.display = 'block';
            } else {
                preview.innerHTML = '';
                preview.style.display = 'none';
            }
        });
    });

    // File upload preview
    var fileInputs = document.querySelectorAll('.file-upload-input');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            var preview = this.closest('.source-panel').querySelector('.upload-preview');
            if (!preview) return;
            var file = this.files[0];
            if (file) {
                preview.innerHTML = '<div class="alert alert-info"><i class="fas fa-check-circle"></i> Selected: ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</div>';
                preview.style.display = 'block';
            }
        });
    });

    // Import search form toggle
    var importSourceRadios = document.querySelectorAll('input[name="source"]');
    importSourceRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            var form = this.closest('form');
            var queryInput = form.querySelector('input[name="query"]');
            if (queryInput) {
                queryInput.placeholder = this.value === 'jikan' ? 'Search MyAnimeList...' : 'Search AniList...';
            }
        });
    });

})();
