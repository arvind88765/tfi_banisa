<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars(GAME_NAME) ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="stage">
  <div class="marquee-frame">
    <header>
      <h1><?= htmlspecialchars(GAME_NAME) ?></h1>
      <div class="lamps"><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
      <div id="day-label" class="tagline">DAY —</div>
    </header>

    <div id="game-area">
      <div id="hint-display"></div>

      <div id="attempts-grid" class="attempts-grid"></div>

      <div class="search-wrap">
        <input id="guess-input" type="text" placeholder="Guess the movie…" autocomplete="off" disabled>
        <div id="guess-results" class="autocomplete-list"></div>
      </div>

      <div id="result-panel" style="display:none;"></div>
    </div>
  </div>
</div>

<script src="assets/js/game.js"></script>
</body>
</html>
