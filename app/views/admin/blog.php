<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $posts */
?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Blog Management</h1>
        <p class="admin-page-lead">Add, edit, publish, and feature public brunch articles.</p>
    </div>
    <a class="btn btn--primary" href="<?= e(admin_url('blog-edit.php')) ?>">
        <i class="fas fa-plus" aria-hidden="true"></i> Add Post
    </a>
</div>

<?php if ($message = flash_get('success')): ?>
    <div class="alert alert--success" role="alert"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($message = flash_get('error')): ?>
    <div class="alert alert--danger" role="alert"><?= e($message) ?></div>
<?php endif; ?>

<div class="admin-table__wrap">
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <i class="fas fa-newspaper" aria-hidden="true"></i>
            <h2>No blog posts yet</h2>
            <p>Create your first brunch article to start building the News &amp; Blogs section.</p>
            <a class="btn btn--primary" href="<?= e(admin_url('blog-edit.php')) ?>">Add Post</a>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">Published Date</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="admin-table__actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <?php
                    $id = (int) ($post['id'] ?? 0);
                    $isPublished = (int) ($post['is_published'] ?? 0) === 1;
                    $isFeatured = (int) ($post['is_featured'] ?? 0) === 1;
                    $publishedAt = (string) ($post['published_at'] ?? '');
                    $publishedLabel = $publishedAt !== '' ? date('M j, Y', strtotime($publishedAt)) : 'Not scheduled';
                    ?>
                    <tr class="admin-blog-row<?= $isFeatured ? ' admin-blog-row--featured' : '' ?><?= !$isPublished ? ' admin-blog-row--draft' : '' ?>">
                        <td class="admin-table__title-cell">
                            <strong><?= e((string) ($post['title'] ?? 'Untitled Post')) ?></strong>
                            <?php if (!empty($post['slug'])): ?>
                                <div class="admin-table__muted">
                                    <a href="<?= e(asset_url('article.php?slug=' . urlencode((string) $post['slug']))) ?>" target="_blank" rel="noopener noreferrer">View</a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= e((string) ($post['category_name'] ?? 'Uncategorized')) ?>
                        </td>
                        <td>
                            <?= e($publishedLabel) ?>
                        </td>
                        <td>
                            <div class="admin-table__badges">
                                <?php if ($isPublished): ?>
                                    <span class="badge badge--success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge--draft">Draft</span>
                                <?php endif; ?>

                                <?php if ($isFeatured): ?>
                                    <span class="badge badge--accent">Featured</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="admin-table__actions">
                            <a class="btn btn--outline btn--sm" href="<?= e(admin_url('blog-edit.php?id=' . $id)) ?>">
                                <i class="fas fa-pen" aria-hidden="true"></i> Edit
                            </a>

                            <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                                <input type="hidden" name="action" value="<?= $isPublished ? 'unpublish' : 'publish' ?>">
                                <button type="submit" class="btn btn--outline btn--sm">
                                    <i class="fas <?= $isPublished ? 'fa-eye-slash' : 'fa-eye' ?>" aria-hidden="true"></i>
                                    <?= $isPublished ? 'Unpublish' : 'Publish' ?>
                                </button>
                            </form>

                            <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                                <input type="hidden" name="action" value="<?= $isFeatured ? 'unfeature' : 'feature' ?>">
                                <button type="submit" class="btn btn--outline btn--sm">
                                    <i class="fas <?= $isFeatured ? 'fa-star-half-alt' : 'fa-star' ?>" aria-hidden="true"></i>
                                    <?= $isFeatured ? 'Unfeature' : 'Feature' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
