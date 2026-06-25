</main>
<?php
$footerClass = isset($footerClass) ? trim((string) $footerClass) : '';
$footerStyle = isset($footerStyle) ? trim((string) $footerStyle) : '';

$resolvedFooterClass = 'site-footer';
if ($footerClass !== '') {
    $resolvedFooterClass .= ' ' . $footerClass;
}
?>
<footer class="<?= e($resolvedFooterClass) ?>"<?= $footerStyle !== '' ? ' style="' . e($footerStyle) . '"' : '' ?>>
    <div class="container site-footer__grid">
        <div>
            <p class="site-footer__brand"><?= e(site_name_display()) ?></p>
            <p class="site-footer__text">Discovering Detroit&apos;s best brunch spots.</p>
        </div>

        <div>
            <h2 class="site-footer__heading">Navigation</h2>
            <ul class="site-footer__list">
                <li><a href="<?= e(asset_url('index.php')) ?>">Home</a></li>
                <li><a href="<?= e(asset_url('blog.php')) ?>">News &amp; Blogs</a></li>
                <li><a href="<?= e(asset_url('gallery.php')) ?>">Gallery</a></li>
                <li><a href="<?= e(asset_url('directory.php')) ?>">Directory</a></li>
                <li><a href="<?= e(asset_url('about.php')) ?>">About</a></li>
                <li><a href="<?= e(asset_url('contact.php')) ?>">Contact</a></li>
            </ul>
        </div>

        <div>
            <h2 class="site-footer__heading">Connect</h2>
            <div class="site-footer__social">
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook" aria-hidden="true"></i></a>
                <a href="#" aria-label="TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
            </div>
            <p class="site-footer__text site-footer__text--spaced">
                <a href="mailto:hello@<?= e(site_domain()) ?>">hello@<?= e(site_domain()) ?></a>
            </p>
        </div>

        <div>
            <h2 class="site-footer__heading">Contact</h2>
            <ul class="site-footer__list site-footer__list--contact">
                <li><i class="fas fa-envelope" aria-hidden="true"></i> hello@<?= e(site_domain()) ?></li>
                <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Detroit, MI</li>
            </ul>
        </div>
    </div>

    <div class="site-footer__bottom">
        <div class="container">
            <p>&copy; <?= (int) date('Y') ?> <?= e(site_domain()) ?>. All rights reserved.</p>
            <p class="site-footer__legal">
                <a href="<?= e(asset_url('privacy.php')) ?>">Privacy</a>
                <span aria-hidden="true"> Â· </span>
                <a href="<?= e(asset_url('terms.php')) ?>">Terms</a>
            </p>
        </div>
    </div>
</footer>
<script src="<?= e(asset_url('assets/js/main.js')) ?>" defer></script>
</body>
</html>
