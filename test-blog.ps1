# Temporary blog page test — delete after use.
$urls = @(
  'http://localhost:8080/blog.php',
  'http://localhost:8080/blog.php?category=brunch-guides',
  'http://localhost:8080/article.php?slug=detroits-most-instagrammable-brunch-spots',
  'http://localhost:8080/article.php?slug=nonexistent-slug-test'
)
foreach ($url in $urls) {
  try {
    $r = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 15
    $c = $r.Content
    Write-Output "URL: $url"
    Write-Output "  STATUS: $($r.StatusCode)"
    Write-Output "  LEN: $($c.Length)"
    Write-Output "  HAS_CANONICAL: $($c.Contains('rel=""canonical""'))"
    Write-Output "  HAS_OG: $($c.Contains('og:title'))"
    Write-Output "  HAS_TWITTER: $($c.Contains('twitter:card'))"
    Write-Output "  HAS_JSONLD: $($c.Contains('application/ld+json'))"
    Write-Output "  HAS_FEATURED: $($c.Contains('featured-article'))"
    Write-Output "  HAS_GRID: $($c.Contains('article-grid'))"
    Write-Output "  HAS_CATS: $($c.Contains('blog-category-link'))"
    Write-Output "  HAS_BREADCRUMB: $($c.Contains('breadcrumb'))"
    Write-Output "  HAS_SHARE: $($c.Contains('article-share'))"
    Write-Output "  HAS_RELATED: $($c.Contains('related-articles'))"
    Write-Output "  HAS_404: $($c.Contains('article-not-found'))"
    Write-Output ""
  } catch {
    Write-Output "URL: $url"
    Write-Output "  ERROR: $($_.Exception.Message)"
    Write-Output ""
  }
}