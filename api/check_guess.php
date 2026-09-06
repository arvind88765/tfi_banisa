<?php
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$roundId = (int)($input['round_id'] ?? 0);
$guessTmdbId = (int)($input['tmdb_id'] ?? 0);
$attemptNumber = (int)($input['attempt_number'] ?? 1);

$db = get_db();
$stmt = $db->prepare("SELECT * FROM rounds WHERE id = ?");
$stmt->execute([$roundId]);
$round = $stmt->fetch();

if (!$round) { echo json_encode(['error' => 'not_found']); exit; }

$maxAttempts = attempts_for_round($round);
$correct = ($guessTmdbId === (int)$round['tmdb_id']);
$gameOver = $correct || $attemptNumber >= $maxAttempts;

$response = [
    'correct' => $correct,
    'game_over' => $gameOver,
    'attempt_number' => $attemptNumber,
    'max_attempts' => $maxAttempts,
];

// only reveal the answer once the game has actually ended
if ($gameOver) {
    $response['movie_title'] = $round['movie_title'];
    $response['movie_year'] = $round['movie_year'];
    $response['poster_path'] = $round['poster_path'];
}

echo json_encode($response);
