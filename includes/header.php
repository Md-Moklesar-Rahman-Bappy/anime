<header class="site-header">
    <div class="header-inner">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <a href="<?= url() ?>" class="site-logo">
                <span class="logo-icon">▶</span>
                <span class="logo-text">Anikoto</span>
            </a>
        </div>

        <div class="header-center">
            <form class="search-form" action="<?= url('filter') ?>" method="GET" autocomplete="off">
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="q" class="search-input" placeholder="Search anime..." value="<?= escape($_GET['q'] ?? '') ?>" id="searchInput" autocomplete="off">
                    <button type="button" class="search-clear" id="searchClear" style="display:none"><i class="fas fa-times"></i></button>
                </div>
                <div class="search-suggestions" id="searchSuggestions"></div>
            </form>
        </div>

        <div class="header-right">
            <a href="<?= url('filter') ?>" class="nav-link" title="Browse"><i class="fas fa-filter"></i><span class="nav-label">Browse</span></a>
            <a href="<?= url('random') ?>" class="nav-link" title="Random"><i class="fas fa-shuffle"></i><span class="nav-label">Random</span></a>
            <?php if (is_auth()): ?>
                <div class="user-dropdown">
                    <button class="nav-link user-btn" id="userMenuBtn">
                        <i class="fas fa-user-circle"></i>
                        <span class="nav-label"><?php $cu = current_user(); if ($cu) echo escape($cu['username']); else echo 'Account' ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="userDropdown">
                        <a href="<?= url('my-list') ?>" class="dropdown-item"><i class="fas fa-list"></i> My List</a>
                        <a href="<?= url('my-list?list=watching') ?>" class="dropdown-item"><i class="fas fa-history"></i> Continue Watching</a>
                        <a href="<?= url('request') ?>" class="dropdown-item"><i class="fas fa-plus-circle"></i> Request Anime</a>
                        <?php
                        $cu = current_user();
                        if ($cu && !empty($cu['role_id'])) {
                            $r = DB::fetch("SELECT level FROM roles WHERE id = ?", [$cu['role_id']]);
                            if ($r && (int)$r['level'] >= 1) {
                                echo '<div class="dropdown-divider"></div>';
                                echo '<a href="' . BASE_URL . '/admin/index.php" class="dropdown-item"><i class="fas fa-crown"></i> Admin Panel</a>';
                            }
                        }
                        ?>
                        <div class="dropdown-divider"></div>
                        <form method="post" action="<?= url('auth/logout') ?>" style="display:inline">
                            <button type="submit" class="dropdown-item" style="background:none;border:none;width:100%;text-align:left;cursor:pointer;color:inherit;font:inherit;padding:8px 16px"><i class="fas fa-sign-out-alt"></i> Logout</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <button class="nav-link auth-btn" id="loginBtn"><i class="fas fa-sign-in-alt"></i><span class="nav-label">Login</span></button>
            <?php endif; ?>
            <div class="lang-switch">
                <button class="lang-btn active" data-lang="en">EN</button>
                <button class="lang-btn" data-lang="jp">JP</button>
            </div>
        </div>
    </div>
</header>
