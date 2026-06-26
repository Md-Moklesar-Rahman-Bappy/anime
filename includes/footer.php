<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="<?= url() ?>" class="footer-logo"><span class="logo-icon">▶</span> Anikoto</a>
            <p class="footer-desc">Watch Anime Online for FREE. Stream your favorite anime series and movies in HD with English subtitles or dubbing.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Browse</h4>
                <a href="<?= url('filter') ?>">All Anime</a>
                <a href="<?= url('filter?status=Currently Airing') ?>">Ongoing</a>
                <a href="<?= url('filter?status=Finished Airing') ?>">Completed</a>
                <a href="<?= url('random') ?>">Random</a>
            </div>
            <div class="footer-col">
                <h4>Genres</h4>
                <?php $fg = array_slice(get_genres(), 0, 6); ?>
                <?php foreach ($fg as $g): ?>
                    <a href="<?= url('genre/' . escape($g['slug'])) ?>"><?= escape($g['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <a href="<?= url('about') ?>">About</a>
                <a href="<?= url('faq') ?>">FAQ</a>
                <a href="<?= url('contact') ?>">Contact</a>
                <a href="<?= url('dmca') ?>">DMCA</a>
                <a href="<?= url('terms') ?>">Terms</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Anikoto. All rights reserved. This site does not store any files on its server.</p>
    </div>
</footer>
