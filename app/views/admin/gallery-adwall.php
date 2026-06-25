<?php

declare(strict_types=1);

/** @var array<string, string> $form */
/** @var array<string, string> $errors */
/** @var string|null $flashSuccess */
/** @var string|null $flashError */

if (!function_exists('gallery_adwall_field_error')) {
    function gallery_adwall_field_error(string $key, array $errors): void
    {
        if (empty($errors[$key])) {
            return;
        }
        ?>
        <span class="admin-form__error"><?= e((string) $errors[$key]) ?></span>
        <?php
    }
}

if (!function_exists('gallery_adwall_image_preview')) {
    function gallery_adwall_image_preview(string $settingKey, string $label, array $form): void
    {
        $value = trim((string) ($form[$settingKey] ?? ''));

        if ($value === '') {
            return;
        }

        $previewId = 'preview_' . preg_replace('/[^a-z0-9_]+/i', '_', $settingKey);
        ?>
        <div class="admin-image-preview" id="<?= e($previewId) ?>">
            <div class="admin-image-preview__media">
                <img src="<?= e($value) ?>" alt="<?= e($label) ?>" loading="lazy">
            </div>
            <div class="admin-image-preview__body">
                <strong><?= e($label) ?></strong>
                <span><?= e($value) ?></span>
                <button
                    type="button"
                    class="btn btn--danger btn--sm admin-image-preview__delete js-adwall-clear-image"
                    data-setting-key="<?= e($settingKey) ?>"
                    data-input-id="<?= e($settingKey) ?>"
                    data-preview-id="<?= e($previewId) ?>"
                >
                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                    Delete Image
                </button>
            </div>
        </div>
        <?php
    }
}

if (!function_exists('gallery_adwall_image_field')) {
    function gallery_adwall_image_field(
        string $settingKey,
        string $uploadKey,
        string $label,
        string $previewLabel,
        string $hint,
        array $form,
        array $errors
    ): void {
        ?>
        <div class="admin-form__field admin-form__field--full">
            <label class="form-label" for="<?= e($settingKey) ?>"><?= e($label) ?></label>
            <input
                type="text"
                id="<?= e($settingKey) ?>"
                name="<?= e($settingKey) ?>"
                class="form-control"
                maxlength="500"
                placeholder="https://... or /uploads/gallery-adwall/..."
                value="<?= e((string) ($form[$settingKey] ?? '')) ?>"
            >
            <span class="admin-form__hint"><?= e($hint) ?></span>

            <?php gallery_adwall_image_preview($settingKey, $previewLabel, $form); ?>

            <label class="admin-form__label" for="<?= e($uploadKey) ?>">Upload <?= e($label) ?></label>
            <input
                type="file"
                id="<?= e($uploadKey) ?>"
                name="<?= e($uploadKey) ?>"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
            >
            <span class="admin-form__hint">Optional backup upload. JPG, PNG, or WEBP. No app-level size limit; server upload limits may still apply.</span>

            <div class="admin-upload-warning">
                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                If this image does not save, test with a small JPG or PNG first. PHP server upload limits may still apply before the site can process the file.
            </div>

            <?php gallery_adwall_field_error($settingKey, $errors); ?>
            <?php gallery_adwall_field_error($uploadKey, $errors); ?>
        </div>
        <?php
    }
}
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Gallery Ad Wall Settings</h1>
        <p class="admin-page-lead">Control the sponsor takeover shown before visitors continue to gallery photos.</p>
    </div>
    <a class="btn btn--outline" href="<?= e(admin_url('galleries.php')) ?>">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        Back to Galleries
    </a>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert--success" role="alert"><?= e($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert--danger" role="alert"><?= e($flashError) ?></div>
<?php endif; ?>

<?php if (!empty($errors['form'])): ?>
    <div class="alert alert--danger" role="alert"><?= e($errors['form']) ?></div>
<?php endif; ?>

<div id="adwallClearImageStatus" class="admin-live-status" hidden></div>

<div class="admin-panel admin-gallery-adwall-settings">
    <form class="admin-form" method="post" action="<?= e(admin_url('gallery-adwall.php')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="admin-form__grid">
            <div class="admin-form__field admin-form__field--full admin-form__section-banner admin-form__section-banner--sponsor">
                <h2 class="admin-form__section-title">Sponsor Takeover Area</h2>
                <p class="admin-form__hint">Controls the main sponsor ad-wall area visitors see before they continue to the gallery.</p>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_sponsor_label">Sponsor Label <span class="admin-form__req">*</span></label>
                <input type="text" id="gallery_adwall_sponsor_label" name="gallery_adwall_sponsor_label" class="form-control" maxlength="80" value="<?= e((string) ($form['gallery_adwall_sponsor_label'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_sponsor_label', $errors); ?>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_sponsor_name">Sponsor Name <span class="admin-form__req">*</span></label>
                <input type="text" id="gallery_adwall_sponsor_name" name="gallery_adwall_sponsor_name" class="form-control" maxlength="120" value="<?= e((string) ($form['gallery_adwall_sponsor_name'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_sponsor_name', $errors); ?>
            </div>

            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="gallery_adwall_sponsor_headline">Sponsor Headline <span class="admin-form__req">*</span></label>
                <input type="text" id="gallery_adwall_sponsor_headline" name="gallery_adwall_sponsor_headline" class="form-control" maxlength="180" value="<?= e((string) ($form['gallery_adwall_sponsor_headline'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_sponsor_headline', $errors); ?>
            </div>

            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="gallery_adwall_sponsor_body">Sponsor Body Text <span class="admin-form__req">*</span></label>
                <textarea id="gallery_adwall_sponsor_body" name="gallery_adwall_sponsor_body" class="form-control" rows="5" maxlength="700"><?= e((string) ($form['gallery_adwall_sponsor_body'] ?? '')) ?></textarea>
                <span class="admin-form__hint">This is the main sponsor/sales message on the left side of the ad wall.</span>
                <?php gallery_adwall_field_error('gallery_adwall_sponsor_body', $errors); ?>
            </div>

            <?php
            gallery_adwall_image_field(
                'gallery_adwall_background_image_url',
                'gallery_adwall_background_image_upload',
                'Background Image URL',
                'Current Sponsor Background Preview',
                'Optional. Use a sponsor flyer, venue image, texture, or campaign visual.',
                $form,
                $errors
            );
            ?>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_overlay_opacity">Background Overlay Strength</label>
                <select id="gallery_adwall_overlay_opacity" name="gallery_adwall_overlay_opacity" class="form-control">
                    <?php
                    $overlayOptions = [
                        '0' => 'None - show image clearly',
                        '0.15' => 'Very Light',
                        '0.25' => 'Light',
                        '0.45' => 'Medium',
                        '0.65' => 'Strong',
                        '0.88' => 'Default dark',
                    ];
                    $currentOverlay = (string) ($form['gallery_adwall_overlay_opacity'] ?? '0.88');
                    ?>
                    <?php foreach ($overlayOptions as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $currentOverlay === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="admin-form__hint">Use None when the sponsor background already has readable text or branding.</span>
                <?php gallery_adwall_field_error('gallery_adwall_overlay_opacity', $errors); ?>
            </div>

            <?php
            gallery_adwall_image_field(
                'gallery_adwall_logo_url',
                'gallery_adwall_logo_upload',
                'Sponsor Logo/Image URL',
                'Current Sponsor Logo/Image Preview',
                'Optional. Add a sponsor logo or brand image.',
                $form,
                $errors
            );
            ?>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_logo_alt">Sponsor Logo/Image Alt Text</label>
                <input type="text" id="gallery_adwall_logo_alt" name="gallery_adwall_logo_alt" class="form-control" maxlength="160" value="<?= e((string) ($form['gallery_adwall_logo_alt'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_logo_alt', $errors); ?>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_cta_label">Sponsor CTA Label</label>
                <input type="text" id="gallery_adwall_cta_label" name="gallery_adwall_cta_label" class="form-control" maxlength="80" placeholder="Visit Sponsor / Reserve Now" value="<?= e((string) ($form['gallery_adwall_cta_label'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_cta_label', $errors); ?>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_cta_url">Sponsor CTA URL</label>
                <input type="url" id="gallery_adwall_cta_url" name="gallery_adwall_cta_url" class="form-control" maxlength="500" placeholder="https://..." value="<?= e((string) ($form['gallery_adwall_cta_url'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_cta_url', $errors); ?>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_continue_label">Gallery Exit Button Label <span class="admin-form__req">*</span></label>
                <input type="text" id="gallery_adwall_continue_label" name="gallery_adwall_continue_label" class="form-control" maxlength="80" value="<?= e((string) ($form['gallery_adwall_continue_label'] ?? '')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_continue_label', $errors); ?>
            </div>

            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="gallery_adwall_provider_note">Gallery Provider Note <span class="admin-form__req">*</span></label>
                <textarea id="gallery_adwall_provider_note" name="gallery_adwall_provider_note" class="form-control" rows="3" maxlength="300"><?= e((string) ($form['gallery_adwall_provider_note'] ?? '')) ?></textarea>
                <?php gallery_adwall_field_error('gallery_adwall_provider_note', $errors); ?>
            </div>

            <div class="admin-form__field admin-form__field--full admin-form__section-banner admin-form__section-banner--footer">
                <h2 class="admin-form__section-title">Gallery Page Footer</h2>
                <p class="admin-form__hint">These options only affect the footer on gallery ad-wall pages.</p>
            </div>

            <?php
            gallery_adwall_image_field(
                'gallery_adwall_footer_background_image_url',
                'gallery_adwall_footer_background_image_upload',
                'Footer Background Image URL',
                'Current Footer Background Preview',
                'Optional. Leave blank to keep the normal dark footer background.',
                $form,
                $errors
            );
            ?>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_footer_overlay_color">Footer Overlay Color</label>
                <input type="color" id="gallery_adwall_footer_overlay_color" name="gallery_adwall_footer_overlay_color" class="form-control" value="<?= e((string) ($form['gallery_adwall_footer_overlay_color'] ?? '#111827')) ?>">
                <?php gallery_adwall_field_error('gallery_adwall_footer_overlay_color', $errors); ?>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_footer_overlay_opacity">Footer Overlay Strength</label>
                <select id="gallery_adwall_footer_overlay_opacity" name="gallery_adwall_footer_overlay_opacity" class="form-control">
                    <?php
                    $footerOverlayOptions = [
                        '0' => 'None - show footer image clearly',
                        '0.15' => 'Very Light',
                        '0.25' => 'Light',
                        '0.45' => 'Medium',
                        '0.65' => 'Strong',
                        '0.82' => 'Default dark',
                        '1' => 'Full overlay',
                    ];
                    $currentFooterOverlay = (string) ($form['gallery_adwall_footer_overlay_opacity'] ?? '0.82');
                    ?>
                    <?php foreach ($footerOverlayOptions as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $currentFooterOverlay === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php gallery_adwall_field_error('gallery_adwall_footer_overlay_opacity', $errors); ?>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_footer_position_x">Footer Image Horizontal Position</label>
                <select id="gallery_adwall_footer_position_x" name="gallery_adwall_footer_position_x" class="form-control">
                    <?php
                    $footerPositionXOptions = [
                        'left' => 'Left',
                        'center' => 'Center',
                        'right' => 'Right',
                    ];
                    $currentFooterPositionX = (string) ($form['gallery_adwall_footer_position_x'] ?? 'center');
                    ?>
                    <?php foreach ($footerPositionXOptions as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $currentFooterPositionX === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="admin-form__hint">Controls whether the footer background image is aligned left, center, or right.</span>
            </div>

            <div class="admin-form__field">
                <label class="form-label" for="gallery_adwall_footer_position_y">Footer Image Vertical Position</label>
                <select id="gallery_adwall_footer_position_y" name="gallery_adwall_footer_position_y" class="form-control">
                    <?php
                    $footerPositionYOptions = [
                        'top' => 'Top',
                        'center' => 'Center',
                        'bottom' => 'Bottom',
                    ];
                    $currentFooterPositionY = (string) ($form['gallery_adwall_footer_position_y'] ?? 'center');
                    ?>
                    <?php foreach ($footerPositionYOptions as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $currentFooterPositionY === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="admin-form__hint">Use Top or Bottom when the important part of the image is being cropped out.</span>
            </div>
        </div>

        <div class="admin-gallery-adwall-preview" aria-label="Ad wall preview">
            <div>
                <span><?= e((string) ($form['gallery_adwall_sponsor_label'] ?? '')) ?></span>
                <strong><?= e((string) ($form['gallery_adwall_sponsor_headline'] ?? '')) ?></strong>
                <p><?= e((string) ($form['gallery_adwall_sponsor_body'] ?? '')) ?></p>
            </div>
            <aside>
                <strong>Gallery Exit Card</strong>
                <p><?= e((string) ($form['gallery_adwall_provider_note'] ?? '')) ?></p>
                <span><?= e((string) ($form['gallery_adwall_continue_label'] ?? '')) ?></span>
            </aside>
        </div>

        <div class="admin-form__actions">
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                Save Ad Wall Settings
            </button>
            <a class="btn btn--outline" href="<?= e(admin_url('galleries.php')) ?>">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('.js-adwall-clear-image');
    if (!button) {
        return;
    }

    const settingKey = button.dataset.settingKey;
    const inputId = button.dataset.inputId;
    const previewId = button.dataset.previewId;
    const status = document.getElementById('adwallClearImageStatus');

    if (!settingKey) {
        return;
    }

    button.disabled = true;

    if (status) {
        status.hidden = false;
        status.className = 'admin-live-status';
        status.textContent = 'Removing image...';
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?= e(csrf_token()) ?>');
    formData.append('action', 'clear_image');
    formData.append('setting_key', settingKey);

    try {
        const response = await fetch('<?= e(admin_url('gallery-adwall.php')) ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const payload = await response.json();

        if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'Image could not be removed.');
        }

        const input = document.getElementById(inputId);
        if (input) {
            input.value = '';
        }

        const preview = document.getElementById(previewId);
        if (preview) {
            preview.remove();
        }

        if (status) {
            status.hidden = false;
            status.className = 'admin-live-status admin-live-status--success';
            status.textContent = payload.message || 'Image removed.';
        }
    } catch (error) {
        button.disabled = false;

        if (status) {
            status.hidden = false;
            status.className = 'admin-live-status admin-live-status--error';
            status.textContent = error.message || 'Image could not be removed.';
        }
    }
});
</script>
