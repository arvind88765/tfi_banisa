<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

function fail($msg) {
    http_response_code(400);
    echo "<p style='font-family:monospace;padding:30px;'>Error: " . htmlspecialchars($msg) . "</p>";
    echo "<p><a href='new_round.php'>&larr; go back</a></p>";
    exit;
}

$mode = $_POST['mode'] ?? '';
$cfg = mode_config($mode);
if (!$cfg) fail('Invalid mode.');

$tmdb_id = (int)($_POST['tmdb_id'] ?? 0);
$movie_title = trim($_POST['movie_title'] ?? '');
$movie_year = trim($_POST['movie_year'] ?? '');
$poster_path = trim($_POST['poster_path'] ?? '');

if (!$tmdb_id || !$movie_title) fail('Please pick the correct answer from the search results.');

if (empty($_FILES['assets']) || empty($_FILES['assets']['name'][0])) {
    fail('Please upload at least one file.');
}

$files = $_FILES['assets'];
$fileCount = count($files['name']);

if ($fileCount < $cfg['min_assets'] || $fileCount > $cfg['max_assets']) {
    fail("This mode needs between {$cfg['min_assets']} and {$cfg['max_assets']} files. You uploaded $fileCount.");
}

$allowedExt = $cfg['kind'] === 'image'
    ? ['jpg','jpeg','png','webp','gif']
    : ['mp4','mov','webm','m4v','mp3','wav','m4a','ogg'];

$db = get_db();
$db->beginTransaction();

try {
    $stmt = $db->prepare("INSERT INTO rounds (mode, tmdb_id, movie_title, movie_year, poster_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$mode, $tmdb_id, $movie_title, $movie_year, $poster_path]);
    $roundId = $db->lastInsertId();

    $dir = __DIR__ . '/../uploads/' . $roundId;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error on file ' . ($i+1));
        }
        $origName = $files['name'][$i];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            throw new Exception("File type .$ext not allowed for this mode.");
        }
        $newName = 'asset_' . ($i+1) . '.' . $ext;
        $dest = $dir . '/' . $newName;
        if (!move_uploaded_file($files['tmp_name'][$i], $dest)) {
            throw new Exception('Failed to move uploaded file ' . ($i+1));
        }
        $relPath = 'uploads/' . $roundId . '/' . $newName;
        $ins = $db->prepare("INSERT INTO round_assets (round_id, sort_order, file_path, kind) VALUES (?, ?, ?, ?)");
        $ins->execute([$roundId, $i+1, $relPath, $cfg['kind']]);
    }

    $db->commit();
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    fail($e->getMessage());
}
