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

<?php if (empty($posts)): ?>
    <div class="empty-state">
        <i class="fas fa-newspaper" aria-hidden="true"></i>
        <h2>No blog posts yet</h2>
        <p>Create your first brunch article to start building the News &amp; Blogs section.</p>
        <a class="btn btn--primary" href="<?= e(admin_url('blog-edit.php')) ?>">Add Post</a>
    </div>
<?php else: ?>
    <div class="admin-table__wrap admin-blog-table-wrap">
        <table class="admin-table admin-blog-table">
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
                    $title = (string) ($post['title'] ?? 'Untitled Post');
                    $category = (string) ($post['category_name'] ?? 'Uncategorized');
                    ?>
                    <tr class="admin-blog-row<?= $isFeatured ? ' admin-blog-row--featured' : '' ?><?= !$isPublished ? ' admin-blog-row--draft' : '' ?>">
                        <td class="admin-table__title-cell">
                            <strong><?= e($title) ?></strong>
                            <?php if (!empty($post['slug'])): ?>
                                <div class="admin-table__muted">
                                    <a href="<?= e(asset_url('article.php?slug=' . urlencode((string) $post['slug']))) ?>" target="_blank" rel="noopener noreferrer">View</a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($category) ?></td>
                        <td><?= e($publishedLabel) ?></td>
                        <td class="admin-blog-status-cell">
                            <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                                <input type="hidden" name="action" value="<?= $isPublished ? 'unpublish' : 'publish' ?>">
                                <?php if ($isPublished): ?>
                                    <button type="submit" class="admin-status-toggle admin-status-toggle--published" title="Click to unpublish">
                                        Published
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="admin-status-toggle admin-status-toggle--draft" title="Click to publish">
                                        Draft
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td class="admin-menu-icon-actions admin-blog-icon-actions">
                            <a class="admin-icon-action" href="<?= e(admin_url('blog-edit.php?id=' . $id)) ?>" aria-label="Edit blog post">
                                <i class="fas fa-pen" aria-hidden="true"></i>
                            </a>

                            <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                                <input type="hidden" name="action" value="<?= $isFeatured ? 'unfeature' : 'feature' ?>">
                                <button
                                    type="submit"
                                    class="admin-icon-action admin-icon-action--feature<?= $isFeatured ? ' admin-icon-action--featured' : '' ?>"
                                    aria-label="<?= $isFeatured ? 'Unfeature blog post' : 'Feature blog post' ?>"
                                    title="<?= $isFeatured ? 'Unfeature' : 'Feature' ?>"
                                >
                                    <i class="fas fa-star" aria-hidden="true"></i>
                                </button>
                            </form>

                            <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                                <input type="hidden" name="action" value="delete">
                                <button
                                    type="submit"
                                    class="admin-icon-action admin-icon-action--danger"
                                    aria-label="Delete blog post"
                                    onclick="return confirm('Delete this blog post? This cannot be undone.');"
                                >
                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-mobile-card-list admin-blog-mobile-list">
        <?php foreach ($posts as $post): ?>
            <?php
            $id = (int) ($post['id'] ?? 0);
            $isPublished = (int) ($post['is_published'] ?? 0) === 1;
            $isFeatured = (int) ($post['is_featured'] ?? 0) === 1;
            $publishedAt = (string) ($post['published_at'] ?? '');
            $publishedLabel = $publishedAt !== '' ? date('M j, Y', strtotime($publishedAt)) : 'Not scheduled';
            $title = (string) ($post['title'] ?? 'Untitled Post');
            $category = (string) ($post['category_name'] ?? 'Uncategorized');
            ?>
            <article class="admin-mobile-card admin-blog-mobile-card">
                <div class="admin-mobile-card__main">
                    <h3><?= e($title) ?></h3>
                    <p><?= e($category) ?> · <?= e($publishedLabel) ?></p>
                    <?php if (!empty($post['slug'])): ?>
                        <p><a href="<?= e(asset_url('article.php?slug=' . urlencode((string) $post['slug']))) ?>" target="_blank" rel="noopener noreferrer">View public post</a></p>
                    <?php endif; ?>
                </div>

                <div class="admin-mobile-card__badges">
                    <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                        <input type="hidden" name="action" value="<?= $isPublished ? 'unpublish' : 'publish' ?>">
                        <?php if ($isPublished): ?>
                            <button type="submit" class="admin-status-toggle admin-status-toggle--published" title="Click to unpublish">
                                Published
                            </button>
                        <?php else: ?>
                            <button type="submit" class="admin-status-toggle admin-status-toggle--draft" title="Click to publish">
                                Draft
                            </button>
                        <?php endif; ?>
                    </form>

                    <?php if ($isFeatured): ?>
                        <span class="badge badge--accent">Featured</span>
                    <?php endif; ?>
                </div>

                <div class="admin-mobile-card__actions">
                    <a class="admin-icon-action" href="<?= e(admin_url('blog-edit.php?id=' . $id)) ?>" aria-label="Edit blog post">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                    </a>

                    <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                        <input type="hidden" name="action" value="<?= $isFeatured ? 'unfeature' : 'feature' ?>">
                        <button
                            type="submit"
                            class="admin-icon-action admin-icon-action--feature<?= $isFeatured ? ' admin-icon-action--featured' : '' ?>"
                            aria-label="<?= $isFeatured ? 'Unfeature blog post' : 'Feature blog post' ?>"
                            title="<?= $isFeatured ? 'Unfeature' : 'Feature' ?>"
                        >
                            <i class="fas fa-star" aria-hidden="true"></i>
                        </button>
                    </form>

                    <form method="post" action="<?= e(admin_url('blog.php')) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= e((string) $id) ?>">
                        <input type="hidden" name="action" value="delete">
                        <button
                            type="submit"
                            class="admin-icon-action admin-icon-action--danger"
                            aria-label="Delete blog post"
                            onclick="return confirm('Delete this blog post? This cannot be undone.');"
                        >
                            <i class="fas fa-trash" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
