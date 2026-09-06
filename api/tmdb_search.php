<?php
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['results' => []]); exit; }

$data = tmdb_search_movies($q);
$results = array_map(function($m) {
    return [
        'id' => $m['id'],
        'title' => $m['title'],
        'year' => !empty($m['release_date']) ? substr($m['release_date'], 0, 4) : '',
        'poster_path' => $m['poster_path'] ?? null,
    ];
}, array_slice($data['results'] ?? [], 0, 8));

echo json_encode(['results' => $results]);
