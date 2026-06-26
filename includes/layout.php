<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle ?? SITE_NAME) ?></title>
    <meta name="description" content="<?= escape(SITE_DESC) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎬</text></svg>">
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>
    <?php require __DIR__ . '/header.php'; ?>
    <div class="main-wrapper">
        <?php require __DIR__ . '/sidebar.php'; ?>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <main class="main-content">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= escape($_SESSION['error']) ?><?php unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= escape($_SESSION['message']) ?><?php unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
    <?php require __DIR__ . '/footer.php'; ?>
    <?php require __DIR__ . '/modals.php'; ?>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
