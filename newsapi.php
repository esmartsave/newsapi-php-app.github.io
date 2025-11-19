<?php
// newsapi.php
require_once __DIR__ . '/config.php';

function callNewsApi(string $endpoint, array $params = []): array {
    $baseUrl = 'https://newsapi.org/v2/';

    // Build query string (no apiKey here; we send it via header)
    $query = http_build_query($params);
    $url = $baseUrl . $endpoint . '?' . $query;

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'X-Api-Key: ' . NEWS_API_KEY,
            'User-Agent: MyNewsApp/1.0'
        ],
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'status' => 'error',
            'message' => 'cURL error: ' . $error
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode !== 200 || !is_array($data)) {
        return [
            'status' => 'error',
            'message' => $data['message'] ?? 'Unknown API error',
        ];
    }

    return $data;
}

/**
 * Get current headlines.
 */
function getTopHeadlines(string $country = 'us', ?string $category = null): array {
    $params = ['country' => $country];

    if ($category) {
        $params['category'] = $category;
    }

    return callNewsApi('top-headlines', $params);
}

/**
 * Search news with user query using /everything.
 */
function searchNews(string $query, string $language = 'en'): array {
    $params = [
        'q' => $query,
        'language' => $language,
        'sortBy' => 'publishedAt',
        'pageSize' => 20,
    ];

    return callNewsApi('everything', $params);
}

?>
