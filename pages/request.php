<?php
$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($title) {
        DB::insert("INSERT INTO anime_requests (user_id, title, description) VALUES (?,?,?)",
            [$user_id, $title, $description]);
        $_SESSION['message'] = 'Your request has been submitted. We\'ll review it soon!';
        redirect('request');
    } else {
        $_SESSION['error'] = 'Title is required.';
    }
}
?>
<section class="section" style="max-width:600px;margin:0 auto;">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-plus-circle"></i> Request Anime</h2>
    </div>
    <?php if (!$user_id): ?>
    <div class="empty-state"><i class="fas fa-lock"></i><p>Please <a href="#" onclick="openModal('loginModal')">log in</a> to request anime.</p></div>
    <?php else: ?>
    <form method="post" class="form-card" style="padding:24px;background:var(--bg-card);border-radius:var(--radius);">
        <div class="form-group">
            <label>Anime Title *</label>
            <input type="text" name="title" class="form-control" required placeholder="Enter anime title...">
        </div>
        <div class="form-group">
            <label>Why do you want this? (optional)</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Tell us about the anime..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
    </form>
    <?php endif; ?>
    <div style="margin-top:20px;">
        <p style="color:var(--text-muted);font-size:0.85rem;">
            <i class="fas fa-info-circle"></i> Check if the anime already exists in our <a href="<?= url('filter') ?>">catalog</a> before submitting.
        </p>
    </div>
</section>
