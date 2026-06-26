<aside class="sidebar" id="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-section">
            <h3 class="sidebar-heading">Quick Access</h3>
            <a href="<?= url('filter?status=Currently Airing') ?>" class="sidebar-link"><i class="fas fa-broadcast-tower"></i> Ongoing</a>
            <a href="<?= url('filter?status=Finished Airing') ?>" class="sidebar-link"><i class="fas fa-check-circle"></i> Completed</a>
            <a href="<?= url('filter?status=Not yet aired') ?>" class="sidebar-link"><i class="fas fa-clock"></i> Upcoming</a>
            <a href="<?= url('random') ?>" class="sidebar-link"><i class="fas fa-shuffle"></i> Random</a>
        </div>

        <div class="sidebar-section">
            <h3 class="sidebar-heading">Genres</h3>
            <?php $genres = get_genres(); ?>
            <?php foreach ($genres as $genre): ?>
                <a href="<?= url('genre/' . escape($genre['slug'])) ?>" class="sidebar-link sidebar-genre">
                    <i class="fas fa-tag"></i> <?= escape($genre['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="sidebar-section">
            <h3 class="sidebar-heading">Type</h3>
            <?php
            $types = ['TV' => 'TV Series', 'Movie' => 'Movies', 'OVA' => 'OVA', 'ONA' => 'ONA', 'Special' => 'Specials'];
            foreach ($types as $key => $label):
            ?>
                <a href="<?= url('filter?type=' . urlencode($key)) ?>" class="sidebar-link"><i class="fas fa-film"></i> <?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <div class="sidebar-section">
            <h3 class="sidebar-heading">A-Z List</h3>
            <div class="az-list">
                <a href="<?= url('filter?az=0-9') ?>" class="az-link">#</a>
                <?php foreach (range('A', 'Z') as $letter): ?>
                    <a href="<?= url('filter?az=' . $letter) ?>" class="az-link"><?= $letter ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</aside>
