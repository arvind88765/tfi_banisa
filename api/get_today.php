<?php
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json');

$round = get_daily_round();
if (!$round) {
    echo json_encode(['error' => 'no_rounds']);
    exit;
}

$db = get_db();
$stmt = $db->prepare("SELECT sort_order, file_path, kind FROM round_assets WHERE round_id = ? ORDER BY sort_order ASC");
$stmt->execute([$round['id']]);
$assets = $stmt->fetchAll();

$maxAttempts = attempts_for_round($round);
$dayNumber = day_number_for_date(date('Y-m-d'));

echo json_encode([
    'round_id' => (int)$round['id'],
    'mode' => $round['mode'],
    'day_number' => $dayNumber,
    'max_attempts' => $maxAttempts,
    'assets' => array_map(function($a) {
        return ['order' => (int)$a['sort_order'], 'path' => $a['file_path'], 'kind' => $a['kind']];
    }, $assets),
    'game_name' => GAME_NAME,
]);
