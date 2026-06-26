<?php
/**
 * @var array  $form          Sticky field values.
 * @var array  $errors        field => message
 * @var array  $neighborhoods id/name pairs for the neighborhood dropdown.
 */
$isEdit = (int) ($form['id'] ?? 0) > 0;
$heading = $isEdit ? 'Edit Venue' : 'Add Venue';
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= e($heading) ?></h1>
        <p class="admin-page-lead">
            <?= $isEdit ? 'Update this venue record.' : 'Create a new public venue profile.' ?>
        </p>
    </div>
    <a class="btn btn--outline" href="<?= e(admin_url('venues.php')) ?>">
        <i class="fa-solid fa-arrow-left"></i> Back to Venues
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
    <form class="admin-form" method="post" action="<?= e(admin_url('venue-edit.php')) ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <?php endif; ?>

        <div class="admin-form__grid">
            <!-- Name -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="name">Name <span class="admin-form__req">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       maxlength="200" required
                       value="<?= e($form['name']) ?>">
                <?php if (!empty($errors['name'])): ?>
                    <span class="admin-form__error"><?= e($errors['name']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Slug -->
            <div class="admin-form__field">
                <label class="form-label" for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control"
                       maxlength="160"
                       value="<?= e($form['slug']) ?>">
                <span class="admin-form__hint">
                    Lowercase letters, numbers, and hyphens. Leave blank to auto-generate from the name.
                </span>
                <?php if (!empty($errors['slug'])): ?>
                    <span class="admin-form__error"><?= e($errors['slug']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Neighborhood -->
            <div class="admin-form__field">
                <label class="form-label" for="neighborhood_id">Neighborhood</label>
                <select id="neighborhood_id" name="neighborhood_id" class="form-control">
                    <option value="">— No neighborhood —</option>
                    <?php foreach ($neighborhoods as $n): ?>
                        <?php $nid = (int) ($n['id'] ?? 0); ?>
                        <option value="<?= $nid ?>"
                            <?= ((string) $nid === (string) $form['neighborhood_id']) ? 'selected' : '' ?>>
                            <?= e((string) ($n['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['neighborhood_id'])): ?>
                    <span class="admin-form__error"><?= e($errors['neighborhood_id']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Price range -->
            <div class="admin-form__field">
                <label class="form-label" for="price_range">Price Range</label>
                <select id="price_range" name="price_range" class="form-control">
                    <option value="">— None —</option>
                    <?php foreach (['$', '$$', '$$$', '$$$$'] as $opt): ?>
                        <option value="<?= e($opt) ?>"
                            <?= ((string) $form['price_range'] === $opt) ? 'selected' : '' ?>>
                            <?= e($opt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['price_range'])): ?>
                    <span class="admin-form__error"><?= e($errors['price_range']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="admin-form__field">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" id="phone" name="phone" class="form-control"
                       maxlength="40"
                       value="<?= e($form['phone']) ?>">
                <?php if (!empty($errors['phone'])): ?>
                    <span class="admin-form__error"><?= e($errors['phone']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Featured sort -->
            <div class="admin-form__field">
                <label class="form-label" for="featured_sort">Featured Sort</label>
                <input type="number" id="featured_sort" name="featured_sort" class="form-control"
                       min="0" step="1"
                       value="<?= e($form['featured_sort']) ?>">
                <span class="admin-form__hint">Lower numbers appear first among featured venues.</span>
                <?php if (!empty($errors['featured_sort'])): ?>
                    <span class="admin-form__error"><?= e($errors['featured_sort']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Website URL -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="website_url">Website URL</label>
                <input type="url" id="website_url" name="website_url" class="form-control"
                       maxlength="500"
                       placeholder="https://..."
                       value="<?= e($form['website_url']) ?>">
                <?php if (!empty($errors['website_url'])): ?>
                    <span class="admin-form__error"><?= e($errors['website_url']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Instagram URL -->
            <div class="admin-form__field">
                <label class="form-label" for="instagram_url">Instagram URL</label>
                <input type="url" id="instagram_url" name="instagram_url" class="form-control"
                       maxlength="500"
                       placeholder="https://..."
                       value="<?= e($form['instagram_url']) ?>">
                <?php if (!empty($errors['instagram_url'])): ?>
                    <span class="admin-form__error"><?= e($errors['instagram_url']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Facebook URL -->
            <div class="admin-form__field">
                <label class="form-label" for="facebook_url">Facebook URL</label>
                <input type="url" id="facebook_url" name="facebook_url" class="form-control"
                       maxlength="500"
                       placeholder="https://..."
                       value="<?= e($form['facebook_url']) ?>">
                <?php if (!empty($errors['facebook_url'])): ?>
                    <span class="admin-form__error"><?= e($errors['facebook_url']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Main image URL -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="main_image_path">Main Image URL</label>
                <input type="url" id="main_image_path" name="main_image_path" class="form-control"
                       maxlength="500"
                       placeholder="https://..."
                       value="<?= e($form['main_image_path']) ?>">
                <span class="admin-form__hint">Direct URL to the main venue image, or upload a profile image below.</span>
                <?php if (!empty($errors['main_image_path'])): ?>
                    <span class="admin-form__error"><?= e($errors['main_image_path']) ?></span>
                <?php endif; ?>

                <?php if (!empty($form['main_image_path'])): ?>
                    <div class="admin-venue-image-preview">
                        <img src="<?= e((string) $form['main_image_path']) ?>" alt="Current profile image preview" loading="lazy">
                    </div>
                <?php endif; ?>

                <div class="admin-form__stacked-field">
                    <label class="form-label" for="main_image_upload">Upload Profile Image</label>
                    <input
                        type="file"
                        id="main_image_upload"
                        name="main_image_upload"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >
                    <span class="admin-form__hint">Optional. JPG, PNG, or WEBP. Max 5MB.</span>
                    <?php if (!empty($errors['main_image_upload'])): ?>
                        <span class="admin-form__error"><?= e((string) $errors['main_image_upload']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interior photos upload -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="interior_image_uploads">Interior Photos</label>
                <input
                    type="file"
                    id="interior_image_uploads"
                    name="interior_image_uploads[]"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                >
                <span class="admin-form__hint">
                    Upload up to 4 interior/profile photos. New uploads replace the current interior photo set.
                </span>

                <?php if (!empty($form['interior_image_paths']) && is_array($form['interior_image_paths'])): ?>
                    <div class="admin-venue-image-preview">
                        <?php foreach ($form['interior_image_paths'] as $imageUrl): ?>
                            <?php if ((string) $imageUrl !== ''): ?>
                                <img src="<?= e((string) $imageUrl) ?>" alt="Current interior photo preview" loading="lazy">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['interior_image_uploads'])): ?>
                    <span class="admin-form__error"><?= e((string) $errors['interior_image_uploads']) ?></span>
                <?php endif; ?>
            </div>
            <!-- Address line 1 -->
            <div class="admin-form__field">
                <label class="form-label" for="address_line1">Address Line 1</label>
                <input type="text" id="address_line1" name="address_line1" class="form-control"
                       maxlength="200"
                       value="<?= e($form['address_line1']) ?>">
                <?php if (!empty($errors['address_line1'])): ?>
                    <span class="admin-form__error"><?= e($errors['address_line1']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Address line 2 -->
            <div class="admin-form__field">
                <label class="form-label" for="address_line2">Address Line 2</label>
                <input type="text" id="address_line2" name="address_line2" class="form-control"
                       maxlength="200"
                       value="<?= e($form['address_line2']) ?>">
                <?php if (!empty($errors['address_line2'])): ?>
                    <span class="admin-form__error"><?= e($errors['address_line2']) ?></span>
                <?php endif; ?>
            </div>

            <!-- City -->
            <div class="admin-form__field">
                <label class="form-label" for="city">City</label>
                <input type="text" id="city" name="city" class="form-control"
                       maxlength="100"
                       placeholder="Detroit"
                       value="<?= e($form['city']) ?>">
                <?php if (!empty($errors['city'])): ?>
                    <span class="admin-form__error"><?= e($errors['city']) ?></span>
                <?php endif; ?>
            </div>

            <!-- State -->
            <div class="admin-form__field">
                <label class="form-label" for="state">State</label>
                <input type="text" id="state" name="state" class="form-control"
                       maxlength="50"
                       placeholder="MI"
                       value="<?= e($form['state']) ?>">
                <?php if (!empty($errors['state'])): ?>
                    <span class="admin-form__error"><?= e($errors['state']) ?></span>
                <?php endif; ?>
            </div>

            <!-- ZIP -->
            <div class="admin-form__field">
                <label class="form-label" for="zip">ZIP</label>
                <input type="text" id="zip" name="zip" class="form-control"
                       maxlength="20"
                       value="<?= e($form['zip']) ?>">
                <?php if (!empty($errors['zip'])): ?>
                    <span class="admin-form__error"><?= e($errors['zip']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Brunch hours note -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="brunch_hours_note">Brunch Hours Note</label>
                <textarea id="brunch_hours_note" name="brunch_hours_note" class="form-control"
                          rows="2"><?= e($form['brunch_hours_note']) ?></textarea>
                <span class="admin-form__hint">Enter real brunch hours only. Leave blank to show N/A publicly. Use short days, e.g. "Sat-Sun 10am-2pm".</span>
            </div>

            <!-- Description -->
            <div class="admin-form__field admin-form__field--full">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="5"><?= e($form['description']) ?></textarea>
            </div>

            <!-- Checkboxes -->
            <div class="admin-form__field admin-form__field--full">
                <div class="admin-checkbox">
                    <input type="checkbox" id="is_published" name="is_published" value="1"
                        <?= !empty($form['is_published']) ? 'checked' : '' ?>>
                    <label for="is_published"><strong>Published</strong> — show this venue on the public Directory page.</label>
                </div>
                <div class="admin-checkbox">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1"
                        <?= !empty($form['is_featured']) ? 'checked' : '' ?>>
                    <label for="is_featured"><strong>Featured</strong> — surface this venue first.</label>
                </div>
            </div>
        </div>

        <div class="admin-form__actions">
            <button type="submit" class="btn btn--primary">
                <i class="fa-solid fa-floppy-disk"></i>
                <?= $isEdit ? 'Save Changes' : 'Add Venue' ?>
            </button>
            <a class="btn btn--outline" href="<?= e(admin_url('venues.php')) ?>">Cancel</a>
        </div>
    </form>
</div>
