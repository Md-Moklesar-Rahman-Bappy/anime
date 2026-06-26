<?php
require_once __DIR__ . '/auth_check.php';
require_permission('settings.manage');
$page_title = 'Telegram Bot';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/TelegramBot.php';

// Ensure subscribers table exists
try {
    DB::query("CREATE TABLE IF NOT EXISTS `telegram_subscribers` (
        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `chat_id` VARCHAR(100) NOT NULL,
        `username` VARCHAR(255) DEFAULT NULL,
        `first_name` VARCHAR(255) DEFAULT NULL,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `chat_id_unique` (`chat_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {}

$bot = TelegramBot::getInstance();
$action = $_GET['action'] ?? 'info';

if ($action === 'set_webhook') {
    $webhook_url = BASE_URL . '/telegram/webhook.php';
    if ($bot->setWebhook($webhook_url)) {
        $_SESSION['admin_success'] = 'Webhook set to: ' . $webhook_url;
    } else {
        $_SESSION['admin_error'] = 'Failed to set webhook. Make sure your site is accessible via HTTPS.';
    }
    redirect(BASE_URL . '/admin/telegram.php');

} elseif ($action === 'delete_webhook') {
    if ($bot->deleteWebhook()) {
        $_SESSION['admin_success'] = 'Webhook deleted.';
    } else {
        $_SESSION['admin_error'] = 'Failed to delete webhook.';
    }
    redirect(BASE_URL . '/admin/telegram.php');

} elseif ($action === 'broadcast' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $photo = $_POST['photo_url'] ?? '';
    if (empty($message)) {
        $_SESSION['admin_error'] = 'Message cannot be empty.';
    } else {
        $sent = $bot->notifySubscribers($message, $photo);
        log_activity('Telegram broadcast', 'telegram', 0, ['message_length' => strlen($message), 'sent' => $sent]);
        $_SESSION['admin_success'] = "Broadcast sent to {$sent} subscribers.";
    }
    redirect(BASE_URL . '/admin/telegram.php');

} elseif ($action === 'test') {
    $test_chat = DB::fetch("SELECT chat_id FROM telegram_subscribers WHERE active = 1 ORDER BY created_at DESC LIMIT 1");
    if ($test_chat) {
        $result = $bot->sendMessage($test_chat['chat_id'], '🧪 <b>Test notification</b> from Anikoto admin panel.');
        if ($result) {
            $_SESSION['admin_success'] = 'Test message sent to ' . htmlspecialchars($test_chat['chat_id']);
        } else {
            $_SESSION['admin_error'] = 'Failed to send test message. Check bot token and webhook.';
        }
    } else {
        $_SESSION['admin_error'] = 'No active subscribers to send test to.';
    }
    redirect(BASE_URL . '/admin/telegram.php');
}

$bot_info = $bot->getMe();
$webhook_info = $bot->getWebhookInfo();
$subscribers = DB::fetchAll("SELECT * FROM telegram_subscribers ORDER BY created_at DESC");
$sub_count = DB::fetch("SELECT COUNT(*) as cnt FROM telegram_subscribers WHERE active = 1")['cnt'] ?? 0;
?>

<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fab fa-telegram"></i></div>
        <div class="stat-info">
            <h3><?= $bot_info['result']['username'] ?? 'N/A' ?></h3>
            <p>Bot Username</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $sub_count ?></h3>
            <p>Active Subscribers</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-globe"></i></div>
        <div class="stat-info">
            <h3><?= ($webhook_info['result']['url'] ?? '') ? '<span style="color:var(--success)">Active</span>' : '<span style="color:var(--danger)">Not Set</span>' ?></h3>
            <p>Webhook Status</p>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Bot Control</h3></div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <a href="telegram.php?action=set_webhook" class="btn btn-primary"><i class="fas fa-plug"></i> Set Webhook</a>
            <a href="telegram.php?action=delete_webhook" class="btn btn-danger"><i class="fas fa-unlink"></i> Remove Webhook</a>
            <a href="telegram.php?action=test" class="btn btn-info"><i class="fas fa-paper-plane"></i> Send Test</a>
        </div>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:12px;">
            Webhook URL: <code><?= BASE_URL ?>/telegram/webhook.php</code>
            <?php if (strpos(BASE_URL, 'localhost') !== false || strpos(BASE_URL, '127.0.0.1') !== false): ?>
            <br><span style="color:var(--warning);"><i class="fas fa-exclamation-triangle"></i> Localhost detected. Telegram requires HTTPS for webhooks. Use the polling script instead: <code>php telegram/poll.php</code></span>
            <?php endif; ?>
        </p>
        <?php if ($webhook_info && !empty($webhook_info['result']['url'])): ?>
        <div style="margin-top:8px;font-size:0.85rem;">
            <p>Pending updates: <strong><?= $webhook_info['result']['pending_update_count'] ?? 0 ?></strong></p>
            <p>Last error: <span style="color:var(--text-muted);"><?= htmlspecialchars($webhook_info['result']['last_error_message'] ?? 'None') ?></span></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Broadcast Message</h3></div>
        <form method="post" action="telegram.php?action=broadcast">
            <div class="form-group">
                <label>Message (HTML supported)</label>
                <textarea name="message" class="form-control" rows="4" placeholder="<b>New Episode!</b> ..."></textarea>
            </div>
            <div class="form-group">
                <label>Photo URL (optional)</label>
                <input type="url" name="photo_url" class="form-control" placeholder="https://...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-bullhorn"></i> Broadcast to <?= $sub_count ?> Subscribers</button>
        </form>
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header"><h3 class="card-title">Subscribers (<?= count($subscribers) ?>)</h3></div>
    <?php if (count($subscribers) > 0): ?>
    <table>
        <thead><tr><th>Chat ID</th><th>Username</th><th>Name</th><th>Status</th><th>Subscribed</th></tr></thead>
        <tbody>
            <?php foreach ($subscribers as $s): ?>
            <tr>
                <td style="font-family:monospace;font-size:0.78rem;"><?= htmlspecialchars($s['chat_id']) ?></td>
                <td><?= htmlspecialchars($s['username'] ?: '-') ?></td>
                <td><?= htmlspecialchars($s['first_name'] ?: '-') ?></td>
                <td><span class="badge <?= $s['active'] ? 'badge-green' : 'badge-gray' ?>"><?= $s['active'] ? 'Active' : 'Inactive' ?></span></td>
                <td style="color:var(--text-muted);font-size:0.78rem;"><?= time_ago($s['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state"><i class="fab fa-telegram"></i><p>No subscribers yet. Send /start to your bot to subscribe.</p></div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
