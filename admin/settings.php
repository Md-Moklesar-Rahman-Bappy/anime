<?php
require_once __DIR__ . '/auth_check.php';
require_permission('settings.manage');
$page_title = 'Site Settings';
require_once __DIR__ . '/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed_keys = ['site_title','site_description','site_keywords','logo_text','admin_email','items_per_page','theme','maintenance_mode','registration_open','default_user_role'];
    foreach ($_POST as $key => $value) {
        if (in_array($key, $allowed_keys, true)) {
            $existing = DB::fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($existing) {
                DB::execute("UPDATE settings SET `value` = ? WHERE `key` = ?", [$value, $key]);
            } else {
                DB::insert("INSERT INTO settings (`key`, `value`) VALUES (?,?)", [$key, $value]);
            }
        }
    }
    log_activity('Updated site settings', 'settings', 0, ['keys' => implode(', ', array_keys($_POST))]);
    $_SESSION['admin_success'] = 'Settings saved successfully.';
    redirect(BASE_URL . '/admin/settings.php');
}

$settings_rows = DB::fetchAll("SELECT * FROM settings");
$settings = [];
foreach ($settings_rows as $row) {
    $settings[$row['key']] = $row['value'];
}
?>
<div class="form-card" style="max-width:700px;">
    <form method="post">
        <div class="form-row">
            <div class="form-group"><label>Site Title</label><input type="text" name="site_title" class="form-control" value="<?= htmlspecialchars($settings['site_title'] ?? SITE_NAME) ?>"></div>
            <div class="form-group"><label>Logo Text</label><input type="text" name="logo_text" class="form-control" value="<?= htmlspecialchars($settings['logo_text'] ?? 'Anikoto') ?>"></div>
        </div>
        <div class="form-group"><label>Site Description</label><textarea name="site_description" class="form-control" rows="2"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Meta Keywords</label><input type="text" name="site_keywords" class="form-control" value="<?= htmlspecialchars($settings['site_keywords'] ?? '') ?>"></div>
        <div class="form-group"><label>Admin Email</label><input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>"></div>
        <div class="form-row">
            <div class="form-group"><label>Items Per Page</label><input type="number" name="items_per_page" class="form-control" value="<?= htmlspecialchars($settings['items_per_page'] ?? '30') ?>" min="6" max="100"></div>
            <div class="form-group"><label>Theme</label>
                <select name="theme" class="form-control"><option value="dark" <?=($settings['theme']??'dark')==='dark'?'selected':''?>>Dark</option><option value="light" <?=($settings['theme']??'')==='light'?'selected':''?>>Light</option></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-check"><input type="checkbox" name="maintenance_mode" value="1" <?=!empty($settings['maintenance_mode'])?'checked':''?>> Maintenance Mode</label></div>
            <div class="form-group"><label class="form-check"><input type="checkbox" name="registration_open" value="1" <?=(!isset($settings['registration_open']) || $settings['registration_open']==='1')?'checked':''?>> Open Registration</label></div>
        </div>
        <div class="form-group"><label>Default User Role</label>
            <select name="default_user_role" class="form-control">
                <?php
                $roles = DB::fetchAll("SELECT * FROM roles ORDER BY level");
                $current_default = $settings['default_user_role'] ?? 'user';
                foreach ($roles as $r):
                    $sel = ($r['slug']===$current_default)?'selected':'';
                ?>
                <option value="<?=$r['slug']?>" <?=$sel?>><?= htmlspecialchars($r['name'])?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top:16px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </form>
</div>
<?php
require_once __DIR__ . '/footer.php';
