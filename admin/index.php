<?php
require_once __DIR__ . '/auth_check.php';
$page_title = 'Dashboard';
require_once __DIR__ . '/layout.php';

$stats = [];
$stats['anime'] = DB::fetch("SELECT COUNT(*) as cnt FROM anime")['cnt'] ?? 0;
$stats['episodes'] = DB::fetch("SELECT COUNT(*) as cnt FROM episodes")['cnt'] ?? 0;
$stats['users'] = DB::fetch("SELECT COUNT(*) as cnt FROM users")['cnt'] ?? 0;
$stats['comments'] = DB::fetch("SELECT COUNT(*) as cnt FROM comments")['cnt'] ?? 0;
$stats['genres'] = DB::fetch("SELECT COUNT(*) as cnt FROM genres")['cnt'] ?? 0;
$stats['views'] = DB::fetch("SELECT SUM(views) as cnt FROM anime")['cnt'] ?? 0;
$stats['reports'] = DB::fetch("SELECT COUNT(*) as cnt FROM reports WHERE status = 'pending'")['cnt'] ?? 0;
$stats['requests'] = DB::fetch("SELECT COUNT(*) as cnt FROM anime_requests WHERE status = 'pending'")['cnt'] ?? 0;

$recent_anime = DB::fetchAll("SELECT id, title, slug, thumbnail, type, status, created_at FROM anime ORDER BY created_at DESC LIMIT 5");
$recent_activity = DB::fetchAll("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10");
$top_anime = DB::fetchAll("SELECT id, title, slug, views, rating FROM anime ORDER BY views DESC LIMIT 5");

// Chart data: user growth (last 7 days)
$user_growth = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $count = DB::fetch("SELECT COUNT(*) as cnt FROM users WHERE DATE(created_at) <= ?", [$date])['cnt'] ?? 0;
    $user_growth[] = ['date' => date('M d', strtotime($date)), 'count' => $count];
}

// Anime by type
$anime_by_type = DB::fetchAll("SELECT type, COUNT(*) as cnt FROM anime WHERE type IS NOT NULL AND type != '' GROUP BY type ORDER BY cnt DESC");

// Anime by status
$anime_by_status = DB::fetchAll("SELECT status, COUNT(*) as cnt FROM anime WHERE status IS NOT NULL AND status != '' GROUP BY status ORDER BY cnt DESC");
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-film"></i></div>
        <div class="stat-info"><h3><?= $stats['anime'] ?></h3><p>Total Anime</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-list"></i></div>
        <div class="stat-info"><h3><?= $stats['episodes'] ?></h3><p>Episodes</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info"><h3><?= $stats['users'] ?></h3><p>Users</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-comments"></i></div>
        <div class="stat-info"><h3><?= $stats['comments'] ?></h3><p>Comments</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-flag"></i></div>
        <div class="stat-info"><h3><?= $stats['reports'] ?></h3><p>Pending Reports</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon cyan"><i class="fas fa-plus-circle"></i></div>
        <div class="stat-info"><h3><?= $stats['requests'] ?></h3><p>Pending Requests</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-tags"></i></div>
        <div class="stat-info"><h3><?= $stats['genres'] ?></h3><p>Genres</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-eye"></i></div>
        <div class="stat-info"><h3><?= number_format($stats['views']) ?></h3><p>Total Views</p></div>
    </div>
</div>

<!-- Charts -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">User Growth (7 days)</h3></div>
        <div style="padding:16px;height:220px;">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Anime by Type</h3></div>
        <div style="padding:16px;height:220px;">
            <canvas id="typeChart"></canvas>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Recent Anime</h3><a href="anime.php" class="btn btn-sm btn-secondary">View All</a></div>
        <?php if (count($recent_anime) > 0): ?>
        <table><thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Date</th></tr></thead><tbody>
            <?php foreach ($recent_anime as $a): ?>
            <tr>
                <td><a href="anime.php?action=edit&id=<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?></a></td>
                <td><span class="badge badge-purple"><?= htmlspecialchars($a['type'] ?? 'N/A') ?></span></td>
                <td><span class="badge badge-green"><?= htmlspecialchars($a['status'] ?? 'N/A') ?></span></td>
                <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('M j, Y', strtotime($a['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-film"></i><p>No anime yet. <a href="anime.php?action=create">Add your first anime</a>.</p></div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Top Viewed</h3></div>
        <?php if (count($top_anime) > 0): ?>
        <table><thead><tr><th>Title</th><th>Views</th><th>Rating</th></tr></thead><tbody>
            <?php foreach ($top_anime as $a): ?>
            <tr>
                <td><a href="anime.php?action=edit&id=<?= $a['id'] ?>"><?= htmlspecialchars($a['title']) ?></a></td>
                <td><?= number_format($a['views']) ?></td>
                <td><?= $a['rating'] ?: 'N/A' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-star"></i><p>No data yet.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php if (count($recent_activity) > 0): ?>
<div class="card" style="margin-top:20px;">
    <div class="card-header"><h3 class="card-title">Recent Activity</h3></div>
    <ul class="activity-list">
        <?php foreach ($recent_activity as $log): ?>
        <?php
        $icon = 'fa-circle';
        $color = 'var(--text-muted)';
        if (strpos($log['action'], 'create') !== false) { $icon = 'fa-plus-circle'; $color = 'var(--success)'; }
        elseif (strpos($log['action'], 'delete') !== false) { $icon = 'fa-trash'; $color = 'var(--danger)'; }
        elseif (strpos($log['action'], 'update') !== false || strpos($log['action'], 'edit') !== false) { $icon = 'fa-edit'; $color = 'var(--info)'; }
        elseif (strpos($log['action'], 'import') !== false) { $icon = 'fa-download'; $color = 'var(--accent)'; }
        ?>
        <li class="activity-item">
            <div class="activity-icon" style="background:<?= str_replace('var', 'rgba', $color) ?>;color:<?= $color ?>;"><i class="fas <?= $icon ?>"></i></div>
            <div class="activity-body">
                <div class="action"><strong><?= htmlspecialchars($log['username'] ?? 'System') ?></strong> <?= htmlspecialchars($log['action']) ?></div>
                <?php if ($log['entity_type']): ?><div class="detail"><?= htmlspecialchars(ucfirst($log['entity_type'])) ?> #<?= $log['entity_id'] ?></div><?php endif; ?>
                <div class="time"><?= time_ago($log['created_at']) ?></div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // User growth chart
    const userCtx = document.getElementById('userGrowthChart');
    if (userCtx) {
        new Chart(userCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($user_growth, 'date')) ?>,
                datasets: [{
                    label: 'Users',
                    data: <?= json_encode(array_column($user_growth, 'count')) ?>,
                    borderColor: '#6c5ce7',
                    backgroundColor: 'rgba(108,92,231,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#a0a0b8' } },
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#a0a0b8', stepSize: 1 } }
                }
            }
        });
    }

    // Anime by type chart
    const typeCtx = document.getElementById('typeChart');
    if (typeCtx) {
        const colors = ['#6c5ce7', '#00b894', '#fdcb6e', '#e17055', '#0984e3', '#00cec9', '#fd79a8'];
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($anime_by_type, 'type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($anime_by_type, 'cnt')) ?>,
                    backgroundColor: colors.slice(0, <?= count($anime_by_type) ?>)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { color: '#a0a0b8' } } }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
