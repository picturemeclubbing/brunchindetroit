<?php

declare(strict_types=1);

/** @var bool $isEdit */
/** @var int $id */
/** @var array<string, string> $form */
/** @var array<string, string> $errors */
/** @var array<int, array<string, mixed>> $categories */
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $isEdit ? 'Edit Blog Post' : 'Add Blog Post' ?></h1>
        <p class="admin-page-lead"><?= $isEdit ? 'Update this public brunch article.' : 'Create a new public brunch article.' ?></p>
    </div>
    <a class="btn btn--outline" href="<?= e(admin_url('blog.php')) ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Blog
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--danger" role="alert">
        <strong>Please fix the highlighted fields.</strong>
        <?php if (!empty($errors['form'])): ?>
            <div><?= e($errors['form']) ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<form class="admin-form admin-blog-edit-form" method="post" action="<?= e(admin_url('blog-edit.php')) ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
    <?php endif; ?>

    <div class="admin-form__grid">
        <div class="admin-form__field admin-form__field--full">
            <label class="admin-form__label" for="title">Title <span class="admin-form__req">*</span></label>
            <input
                class="admin-form__input"
                type="text"
                id="title"
                name="title"
                value="<?= e($form['title'] ?? '') ?>"
                maxlength="255"
                required
            >
            <?php if (!empty($errors['title'])): ?>
                <span class="admin-form__error"><?= e($errors['title']) ?></span>
            <?php endif; ?>
        </div>

        <div class="admin-form__field">
            <label class="admin-form__label" for="slug">Slug</label>
            <input
                class="admin-form__input"
                type="text"
                id="slug"
                name="slug"
                value="<?= e($form['slug'] ?? '') ?>"
                maxlength="160"
            >
            <span class="admin-form__hint">Lowercase letters, numbers, and hyphens. Leave blank to auto-generate from the title.</span>
            <?php if (!empty($errors['slug'])): ?>
                <span class="admin-form__error"><?= e($errors['slug']) ?></span>
            <?php endif; ?>
        </div>

        <div class="admin-form__field">
            <label class="admin-form__label" for="category_id">Category</label>
            <select class="admin-form__input" id="category_id" name="category_id">
                <option value="">Uncategorized</option>
                <?php foreach ($categories as $category): ?>
                    <?php $categoryId = (string) ($category['id'] ?? ''); ?>
                    <option value="<?= e($categoryId) ?>" <?= (string) ($form['category_id'] ?? '') === $categoryId ? 'selected' : '' ?>>
                        <?= e((string) ($category['name'] ?? 'Untitled Category')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['category_id'])): ?>
                <span class="admin-form__error"><?= e($errors['category_id']) ?></span>
            <?php endif; ?>
        </div>
        <div class="admin-form__field admin-form__field--full">
            <label class="admin-form__label" for="featured_image_path">Featured Image URL / Path</label>
            <input
                class="admin-form__input"
                type="text"
                id="featured_image_path"
                name="featured_image_path"
                value="<?= e($form['featured_image_path'] ?? '') ?>"
                maxlength="500"
                placeholder="https://... or /assets/images/..."
            >
            <span class="admin-form__hint">Use an http(s) image URL or a root-relative image path. Uploading a file below will replace this value on save.</span>
            <?php if (!empty($errors['featured_image_path'])): ?>
                <span class="admin-form__error"><?= e($errors['featured_image_path']) ?></span>
            <?php endif; ?>

            <?php if (!empty($form['featured_image_path'])): ?>
                <div class="admin-blog-image-preview">
                    <span class="admin-blog-image-preview__label">Current image</span>
                    <img src="<?= e($form['featured_image_path']) ?>" alt="" loading="lazy">
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-form__field admin-form__field--full">
            <label class="admin-form__label" for="featured_image_upload">Upload Featured Image</label>
            <input
                class="admin-form__input"
                type="file"
                id="featured_image_upload"
                name="featured_image_upload"
                accept="image/jpeg,image/png,image/webp"
            >
            <span class="admin-form__hint">Optional. JPG, PNG, or WEBP. Max 5MB. If selected, this upload becomes the featured image.</span>
            <?php if (!empty($errors['featured_image_upload'])): ?>
                <span class="admin-form__error"><?= e($errors['featured_image_upload']) ?></span>
            <?php endif; ?>
        </div>

        <div class="admin-form__field">
            <label class="admin-form__label" for="published_at">Published At</label>
            <input
                class="admin-form__input"
                type="datetime-local"
                id="published_at"
                name="published_at"
                value="<?= e($form['published_at'] ?? '') ?>"
            >
            <span class="admin-form__hint">Leave blank when publishing to use the current date/time.</span>
            <?php if (!empty($errors['published_at'])): ?>
                <span class="admin-form__error"><?= e($errors['published_at']) ?></span>
            <?php endif; ?>
        </div>

        <div class="admin-form__field">
            <label class="admin-form__label">Visibility</label>

            <label class="admin-check">
                <input type="checkbox" name="is_published" value="1" <?= (string) ($form['is_published'] ?? '0') === '1' ? 'checked' : '' ?>>
                <span><strong>Published</strong> - show this article publicly.</span>
            </label>

            <label class="admin-check">
                <input type="checkbox" name="is_featured" value="1" <?= (string) ($form['is_featured'] ?? '0') === '1' ? 'checked' : '' ?>>
                <span><strong>Featured</strong> - prioritize this article in the homepage spotlight.</span>
            </label>
        </div>

        <div class="admin-form__field admin-form__field--full">
            <label class="admin-form__label" for="excerpt">Intro / Card &amp; SEO Summary</label>
            <textarea
                class="admin-form__input"
                id="excerpt"
                name="excerpt" rows="3" maxlength="180"
            ><?= e($form['excerpt'] ?? '') ?></textarea>
            <span class="admin-form__hint">80-180 characters. Used for blog cards, featured sliders, homepage spotlight, and SEO/social previews. Drafts may leave this blank.</span>
            <?php if (!empty($errors['excerpt'])): ?>
                <span class="admin-form__error"><?= e($errors['excerpt']) ?></span>
            <?php endif; ?>
        </div>

        <div class="admin-form__field admin-form__field--full">
            <label class="admin-form__label" for="body">Body</label>
            <textarea
                class="admin-form__input"
                id="body"
                name="body"
                rows="14"
            ><?= e($form['body'] ?? '') ?></textarea>
            <span class="admin-form__hint">HTML is allowed. The intro field above controls cards, featured sliders, and SEO/social previews.</span>
        </div>
    </div>

    <div class="admin-form__actions">
        <button type="submit" class="btn btn--primary">
            <i class="fas fa-save" aria-hidden="true"></i>
            <?= $isEdit ? 'Save Changes' : 'Add Post' ?>
        </button>
        <a class="btn btn--outline" href="<?= e(admin_url('blog.php')) ?>">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var intro = document.getElementById('excerpt');
    if (!intro || document.querySelector('.admin-blog-intro-counter')) {
        return;
    }

    var counter = document.createElement('div');
    counter.className = 'admin-blog-intro-counter';
    intro.insertAdjacentElement('afterend', counter);

    function updateIntroCounter() {
        var length = intro.value.trim().length;
        counter.textContent = length + ' / 180 characters';

        counter.classList.remove('is-low', 'is-good', 'is-over');

        if (length === 0) {
            return;
        }

        if (length < 80) {
            counter.classList.add('is-low');
        } else if (length <= 180) {
            counter.classList.add('is-good');
        } else {
            counter.classList.add('is-over');
        }
    }

    intro.addEventListener('input', updateIntroCounter);
    updateIntroCounter();
});
</script>
