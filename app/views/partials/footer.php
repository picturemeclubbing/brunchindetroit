<?php
$footerClass = isset($footerClass) ? trim((string) $footerClass) : '';
$footerStyle = isset($footerStyle) ? trim((string) $footerStyle) : '';

$resolvedFooterClass = 'site-footer';
if ($footerClass !== '') {
    $resolvedFooterClass .= ' ' . $footerClass;
}
?>
</main>
<footer class="<?= e($resolvedFooterClass) ?>"<?= $footerStyle !== '' ? ' style="' . e($footerStyle) . '"' : '' ?>>
    <div class="container site-footer__grid site-footer__grid--modern">
        <div class="site-footer__brand-column">
            <p class="site-footer__brand">BrunchInDetroit</p>
            <p class="site-footer__text">
                Discovering Detroit&apos;s best brunch spots, galleries, menus, and weekend moves.
            </p>

            <div class="site-footer__social">
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook" aria-hidden="true"></i></a>
                <a href="#" aria-label="TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
        </div>

        <div>
            <h2 class="site-footer__heading">Explore</h2>
            <ul class="site-footer__list">
                <li><a href="<?= e(asset_url('directory.php')) ?>">Directory</a></li>
                <li><a href="<?= e(asset_url('gallery.php')) ?>">Galleries</a></li>
                <li><a href="<?= e(asset_url('blog.php')) ?>">News</a></li>
                <li><a href="<?= e(asset_url('contact.php')) ?>">List your spot</a></li>
            </ul>
        </div>

        <div>
            <h2 class="site-footer__heading">Neighborhoods</h2>
            <ul class="site-footer__list">
                <li><a href="<?= e(asset_url('directory.php?q=Corktown')) ?>">Corktown</a></li>
                <li><a href="<?= e(asset_url('directory.php?q=Downtown')) ?>">Downtown</a></li>
                <li><a href="<?= e(asset_url('directory.php?q=Midtown')) ?>">Midtown</a></li>
                <li><a href="<?= e(asset_url('directory.php?q=Eastern%20Market')) ?>">Eastern Market</a></li>
            </ul>
        </div>

        <div>
            <h2 class="site-footer__heading">Contact</h2>
            <ul class="site-footer__list site-footer__list--contact">
                <li><i class="fas fa-envelope" aria-hidden="true"></i> <a href="mailto:hello@<?= e(site_domain()) ?>">hello@<?= e(site_domain()) ?></a></li>
                <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Detroit, MI</li>
            </ul>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="container site-footer__bottom-inner">
            <p>&copy; <?= (int) date('Y') ?> <?= e(site_domain()) ?>. All rights reserved.</p>
            <p class="site-footer__legal">
                <a href="<?= e(asset_url('privacy.php')) ?>">Privacy</a>
                <span aria-hidden="true">&middot;</span>
                <a href="<?= e(asset_url('terms.php')) ?>">Terms</a>
            </p>
        </div>
    </div>
</footer>

<?php require APP_ROOT . '/views/partials/rsvp-modal.php'; ?>

<script src="<?= e(asset_url('assets/js/main.js')) ?>"></script>
<script src="<?= e(asset_url('assets/js/rsvp.js')) ?>"></script>
<script src="<?= e(asset_url('assets/js/premium-lightbox.js')) ?>"></script>
</body>
</html>
