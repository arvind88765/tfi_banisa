<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Round — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
  <div class="admin-header">
    <h1>New Round</h1>
    <a class="btn btn-ghost" href="index.php">← Back</a>
  </div>

  <form id="round-form" action="save_round.php" method="post" enctype="multipart/form-data">

    <label class="field-label">Game mode</label>
    <div class="mode-tabs">
      <label><input type="radio" name="mode" value="audio" checked> Audio (up to 10 clips)</label>
      <label><input type="radio" name="mode" value="image"> Pictures (3 images)</label>
      <label><input type="radio" name="mode" value="music"> Music (1 soundtrack clip)</label>
    </div>

    <label class="field-label">Correct answer (search TMDb)</label>
    <input type="text" id="answer-search" placeholder="Start typing a movie title…" autocomplete="off">
    <div id="answer-results" class="autocomplete-list"></div>
    <div id="answer-picked" class="picked-answer" style="display:none;">
      <img id="picked-poster" class="mini-poster">
      <span id="picked-title"></span>
      <button type="button" id="clear-picked" class="link-danger">change</button>
    </div>
    <input type="hidden" name="tmdb_id" id="tmdb_id" required>
    <input type="hidden" name="movie_title" id="movie_title">
    <input type="hidden" name="movie_year" id="movie_year">
    <input type="hidden" name="poster_path" id="poster_path">

    <label class="field-label">Upload files</label>
    <div id="asset-slots"></div>
    <button type="button" id="add-asset-btn" class="btn btn-ghost">+ Add another</button>

    <div style="margin-top:28px;">
      <button type="submit" class="btn btn-large">Save round</button>
    </div>
  </form>
</div>

<script src="../assets/js/admin.js"></script>
</body>
</html>
