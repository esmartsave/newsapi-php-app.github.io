<?php
require_once __DIR__ . '/newsapi.php';

// Decide what to do based on GET parameters
$action = $_GET['action'] ?? 'headlines'; // default: show headlines
$query = trim($_GET['q'] ?? '');

// Prepare a variable to hold the API response
$articles = [];
$error = null;
$title = '';

if ($action === 'search' && $query !== '') {
    $result = searchNews($query, 'en');
    if (($result['status'] ?? '') === 'ok') {
        $articles = $result['articles'];
        $title = "Search results for: " . htmlspecialchars($query);
    } else {
        $error = $result['message'] ?? 'Unknown error from NewsAPI.';
    }
} else {
    // get current headlines
    $result = getTopHeadlines('us');
    if (($result['status'] ?? '') === 'ok') {
        $articles = $result['articles'];
        $title = "Current Top Headlines (US)";
    } else {
        $error = $result['message'] ?? 'Unknown error from NewsAPI.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>News Browser</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light">
<div class="container py-4">

    <h1 class="mb-4">Browse the news</h1>

    <!-- Controls -->
    <form class="card mb-4 p-3" method="get" action="index.php">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <button type="submit" name="action" value="headlines" class="btn btn-primary w-100">
                    Showing the current headlines
                </button>
            </div>
            <div class="col-md-4">
                <input
                    type="text"
                    name="q"
                    value="<?php echo htmlspecialchars($query); ?>"
                    class="form-control"
                    placeholder="Search news (e.g. bitcoin, sports)..."
                >
            </div>
            <div class="col-md-4">
                <button type="submit" name="action" value="search" class="btn btn-outline-secondary w-100">
                    Search news here
                </button>
            </div>
        </div>
    </form>

    <!-- Title / status -->
    <?php if ($error): ?>
        <div class="alert alert-danger">
            Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php else: ?>
        <h2 class="h4 mb-3"><?php echo htmlspecialchars($title); ?></h2>
    <?php endif; ?>

    <!-- Results -->
    <div class="row g-3">
        <?php if (!$error && empty($articles)): ?>
            <p>No articles found.</p>
        <?php endif; ?>

        <?php foreach ($articles as $article): ?>
            <div class="col-md-4">
                <div class="card h-100 article-card">
                    <?php if (!empty($article['urlToImage'])): ?>
                        <img
                            src="<?php echo htmlspecialchars($article['urlToImage']); ?>"
                            class="card-img-top"
                            alt="Article image"
                        >
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($article['title'] ?? '(No title)'); ?>
                        </h5>
                        <?php if (!empty($article['description'])): ?>
                            <p class="card-text">
                                <?php echo htmlspecialchars($article['description']); ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-muted small mt-auto">
                            <?php
                            $sourceName = $article['source']['name'] ?? 'Unknown source';
                            $publishedAt = $article['publishedAt'] ?? '';
                            $dateText = $publishedAt
                                ? date('Y-m-d H:i', strtotime($publishedAt))
                                : '';
                            echo htmlspecialchars($sourceName . ($dateText ? ' – ' . $dateText : ''));
                            ?>
                        </p>
                        <?php if (!empty($article['url'])): ?>
                            <a
                                href="<?php echo htmlspecialchars($article['url']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-sm btn-primary mt-2"
                            >
                                Read the full article here
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
</body>
</html>
