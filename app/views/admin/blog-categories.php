<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $categories */
/** @var array<string, string> $form */
/** @var array<string, string> $errors */
/** @var array<string, mixed>|null $editCategory */

$isEdit = !empty($form['id']);
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Blog Categories</h1>
        <p class="admin-page-lead">Manage category labels used by News &amp; Blogs filters, article cards, and blog organization.</p>
    </div>

    <div class="admin-page-actions">
        <a class="btn btn--outline" href="<?= e(admin_url('blog.php')) ?>">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Blog
        </a>
        <a class="btn btn--primary" href="<?= e(admin_url('blog-edit.php')) ?>">
            <i class="fas fa-plus" aria-hidden="true"></i> Add Post
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert--danger" role="alert">
        <strong>Please fix the highlighted fields.</strong>
        <?php if (!empty($errors['form'])): ?>
            <div><?= e($errors['form']) ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="admin-category-layout">
    <section class="admin-category-panel">
        <h2><?= $isEdit ? 'Edit Category' : 'Add Category' ?></h2>
        <p class="admin-category-panel__lead">
            Slugs are used in public URLs like <code>/blog.php?category=brunch-guides</code>.
        </p>

        <form class="admin-form admin-category-form" method="post" action="<?= e(admin_url('blog-categories.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e($form['id']) ?>">
            <?php endif; ?>

            <div class="admin-form__field">
                <label class="admin-form__label" for="name">Name <span class="admin-form__req">*</span></label>
                <input
                    class="admin-form__input"
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($form['name'] ?? '') ?>"
                    maxlength="120"
                    required
                >
                <?php if (!empty($errors['name'])): ?>
                    <span class="admin-form__error"><?= e($errors['name']) ?></span>
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
                    maxlength="120"
                >
                <span class="admin-form__hint">Leave blank to auto-generate from the name. Use lowercase letters, numbers, and hyphens.</span>
                <?php if (!empty($errors['slug'])): ?>
                    <span class="admin-form__error"><?= e($errors['slug']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-form__field">
                <label class="admin-form__label" for="sort_order">Sort Order</label>
                <input
                    class="admin-form__input"
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    value="<?= e($form['sort_order'] ?? '0') ?>"
                    step="1"
                >
                <span class="admin-form__hint">Lower numbers appear first.</span>
                <?php if (!empty($errors['sort_order'])): ?>
                    <span class="admin-form__error"><?= e($errors['sort_order']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-form__actions">
                <button type="submit" class="btn btn--primary">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    <?= $isEdit ? 'Save Category' : 'Add Category' ?>
                </button>

                <?php if ($isEdit): ?>
                    <a class="btn btn--outline" href="<?= e(admin_url('blog-categories.php')) ?>">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="admin-category-panel admin-category-panel--list">
        <div class="admin-category-panel__header">
            <div>
                <h2>Current Categories</h2>
                <p class="admin-category-panel__lead">Categories with assigned posts cannot be deleted.</p>
            </div>
            <span class="badge badge--muted"><?= count($categories) ?> total</span>
        </div>

        <?php if (empty($categories)): ?>
            <div class="admin-empty-state">
                <h2>No categories yet</h2>
                <p>Add a category to organize blog posts.</p>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Category</th>
                            <th scope="col">Slug</th>
                            <th scope="col">Sort</th>
                            <th scope="col">Posts</th>
                            <th scope="col" class="admin-table__actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <?php
                            $categoryId = (int) ($category['id'] ?? 0);
                            $postCount = (int) ($category['post_count'] ?? 0);
                            ?>
                            <tr>
                                <td class="admin-table__title-cell">
                                    <strong><?= e((string) ($category['name'] ?? 'Untitled Category')) ?></strong>
                                </td>
                                <td><code><?= e((string) ($category['slug'] ?? '')) ?></code></td>
                                <td><?= e((string) ($category['sort_order'] ?? '0')) ?></td>
                                <td>
                                    <span class="badge <?= $postCount > 0 ? 'badge--success' : 'badge--draft' ?>">
                                        <?= $postCount ?> <?= $postCount === 1 ? 'post' : 'posts' ?>
                                    </span>
                                </td>
                                <td class="admin-table__actions">
                                    <a class="btn btn--sm btn--outline" href="<?= e(admin_url('blog-categories.php?edit=' . $categoryId)) ?>">
                                        Edit
                                    </a>

                                    <?php if ($postCount === 0): ?>
                                        <form method="post" action="<?= e(admin_url('blog-categories.php')) ?>" onsubmit="return confirm('Delete this category?');">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= e((string) $categoryId) ?>">
                                            <button type="submit" class="btn btn--sm btn--danger">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn--sm btn--outline" disabled title="Categories with posts cannot be deleted.">
                                            Locked
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>