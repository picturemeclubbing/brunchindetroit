<?php
/**
 * @var array  $form     Sticky field values.
 * @var array  $errors   field => message
 * @var array  $venues   id/name pairs for the venue dropdown.
 */
$isEdit = (int) ($form['id'] ?? 0) > 0;
$heading = $isEdit ? 'Edit Gallery' : 'Add Gallery';
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($heading) ?></h1>
        <p class="admin-page-lead">
            <?= $isEdit ? 'Update this gallery record.' : 'Create a new public gallery card.' ?>
        </p>
    </div>
    <a class="btn btn--outline" href="<?= e(admin_url('galleries.php')) ?>">
        <i class="fa-solid fa-arrow-left"></i> Back to Galleries
    </a>
</div>

<?php if (!empty($errors['form'])): ?>
    <div class="alert alert--danger" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= e($errors['form']) ?>
    </div>
<?php elseif (!empty($errors)): ?>
    <div class="alert alert--danger" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        Please correct the highlighted fields below.
    </div>
<?php endif; ?>

<div class="admin-panel">
    <form class="admin-form" method="post" action="<?= e(admin_url('gallery-edit.php')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <?php endif; ?>

        <div class="admin-form__grid">
            <!-- Title -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="title">Title <span class="admin-form__req">*</span></label>
                <input type="text" id="title" name="title" class="form-control"
                       maxlength="255" required
                       value="<?= e($form['title']) ?>">
                <?php if (!empty($errors['title'])): ?>
                    <span class="admin-form__error"><?= e($errors['title']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Slug -->
            <div class="admin-form__field">
                <label class="form-label" for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control"
                       maxlength="160"
                       value="<?= e($form['slug']) ?>">
                <span class="admin-form__hint">
                    Lowercase letters, numbers, and hyphens. Leave blank to auto-generate from the title.
                </span>
                <?php if (!empty($errors['slug'])): ?>
                    <span class="admin-form__error"><?= e($errors['slug']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Venue -->
            <div class="admin-form__field">
                <label class="form-label" for="venue_id">Venue</label>
                <select id="venue_id" name="venue_id" class="form-control">
                    <option value="">— No linked venue —</option>
                    <?php foreach ($venues as $v): ?>
                        <?php $vid = (int) ($v['id'] ?? 0); ?>
                        <option value="<?= $vid ?>"
                            <?= ((string) $vid === (string) $form['venue_id']) ? 'selected' : '' ?>>
                            <?= e((string) ($v['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['venue_id'])): ?>
                    <span class="admin-form__error"><?= e($errors['venue_id']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Event date -->
            <div class="admin-form__field">
                <label class="form-label" for="event_date">Event Date</label>
                <input type="date" id="event_date" name="event_date" class="form-control"
                       value="<?= e($form['event_date']) ?>">
                <?php if (!empty($errors['event_date'])): ?>
                    <span class="admin-form__error"><?= e($errors['event_date']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Location label -->
            <div class="admin-form__field">
                <label class="form-label" for="location_label">Location Label</label>
                <input type="text" id="location_label" name="location_label" class="form-control"
                       maxlength="200"
                       value="<?= e($form['location_label']) ?>">
                <span class="admin-form__hint">Shown on the gallery card (e.g. neighborhood or city).</span>
                <?php if (!empty($errors['location_label'])): ?>
                    <span class="admin-form__error"><?= e($errors['location_label']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Cover image URL -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="cover_image_path">Cover Image URL</label>
                <input type="url" id="cover_image_path" name="cover_image_path" class="form-control"
                       maxlength="500"
                       placeholder="https://..."
                       value="<?= e($form['cover_image_path']) ?>">
                <span class="admin-form__hint">Direct URL to the cover image (e.g. on SmugMug or a CDN).</span>
                <?php if (!empty($errors['cover_image_path'])): ?>
                    <span class="admin-form__error"><?= e($errors['cover_image_path']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Gallery URL -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="gallery_url">Gallery URL</label>
                <input type="url" id="gallery_url" name="gallery_url" class="form-control"
                       maxlength="500"
                       placeholder="https://..."
                       value="<?= e($form['gallery_url']) ?>">
                <span class="admin-form__hint">
                    Required for the public card to link out to SmugMug or another gallery host.
                </span>
                <?php if (!empty($errors['gallery_url'])): ?>
                    <span class="admin-form__error"><?= e($errors['gallery_url']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?= e($form['description']) ?></textarea>
            </div>

            <!-- Checkboxes -->
            <div class="admin-form__field admin-form__field--full">
                <div class="admin-checkbox">
                    <input type="checkbox" id="is_published" name="is_published" value="1"
                        <?= !empty($form['is_published']) ? 'checked' : '' ?>>
                    <label for="is_published"><strong>Published</strong> — show this gallery on the public Gallery page.</label>
                </div>
                <div class="admin-checkbox">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                        <?= !empty($form['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured"><strong>Featured</strong> — surface this gallery first.</label>
                </div>
            </div>
        </div>

        <div class="admin-form__actions">
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $isEdit ? 'Save Changes' : 'Add Gallery' ?>
            </button>
            <a class="btn btn--outline" href="<?= e(admin_url('galleries.php')) ?>">Cancel</a>
        </div>
    </form>
</div>