<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$db = get_db();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // remove uploaded files first
    $stmt = $db->prepare("SELECT file_path FROM round_assets WHERE round_id = ?");
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $row) {
        $full = __DIR__ . '/../' . $row['file_path'];
        if (file_exists($full)) unlink($full);
    }
    $db->prepare("DELETE FROM rounds WHERE id = ?")->execute([$id]);
    header('Location: index.php');
    exit;
}

$rounds = $db->query("SELECT * FROM rounds ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — <?= htmlspecialchars(GAME_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
  <div class="admin-header">
    <h1><?= htmlspecialchars(GAME_NAME) ?> — Admin</h1>
    <div>
      <a class="btn" href="new_round.php">+ New Round</a>
      <a class="btn btn-ghost" href="logout.php">Log out</a>
    </div>
  </div>

  <table class="round-table">
    <thead>
      <tr><th>Poster</th><th>Movie</th><th>Mode</th><th>Assets</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($rounds as $r):
        $stmt = $db->prepare("SELECT COUNT(*) c FROM round_assets WHERE round_id = ?");
        $stmt->execute([$r['id']]);
        $count = $stmt->fetch()['c'];
      ?>
      <tr>
        <td>
          <?php if ($r['poster_path']): ?>
            <img class="mini-poster" src="https://image.tmdb.org/t/p/w92<?= htmlspecialchars($r['poster_path']) ?>">
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($r['movie_title']) ?> <span class="dim">(<?= htmlspecialchars($r['movie_year']) ?>)</span></td>
        <td><span class="tag tag-<?= $r['mode'] ?>"><?= strtoupper($r['mode']) ?></span></td>
        <td><?= $count ?> file<?= $count==1?'':'s' ?></td>
        <td><?= $r['is_active'] ? 'Active' : 'Hidden' ?></td>
        <td><a class="link-danger" href="?delete=<?= $r['id'] ?>" onclick="return confirm('Delete this round and its files?')">Delete</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rounds): ?>
        <tr><td colspan="6" class="dim" style="text-align:center;padding:30px;">No rounds yet — create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
