<?php
require_once __DIR__ . '/db.php';

// ---- Mode configuration ----
// audio: 1-10 clip assets uploaded by admin, attempts = number of assets
// image: exactly 3 image assets, attempts = 3, revealed one per wrong guess
// music: exactly 1 clip asset, attempts = MUSIC_MODE_ATTEMPTS (retries on same clip)
function mode_config($mode) {
    switch ($mode) {
        case 'audio': return ['min_assets' => 1, 'max_assets' => 10, 'kind' => 'clip'];
        case 'image': return ['min_assets' => 3, 'max_assets' => 3, 'kind' => 'image'];
        case 'music': return ['min_assets' => 1, 'max_assets' => 1, 'kind' => 'clip'];
        default: return null;
    }
}

function attempts_for_round($round) {
    if ($round['mode'] === 'music') return MUSIC_MODE_ATTEMPTS;
    // audio / image: attempts = number of assets that round has
    $db = get_db();
    $stmt = $db->prepare("SELECT COUNT(*) c FROM round_assets WHERE round_id = ?");
    $stmt->execute([$round['id']]);
    return max(1, (int)$stmt->fetch()['c']);
}

// ---- Day numbering ----
function day_number_for_date($dateStr) {
    $launch = new DateTime(LAUNCH_DATE);
    $today = new DateTime($dateStr);
    $diff = $launch->diff($today);
    $days = (int)$diff->format('%a');
    return ($today >= $launch) ? $days + 1 : 0;
}

// ---- Pick (or assign) today's round, deterministic for everyone ----
function get_daily_round($dateStr = null) {
    $db = get_db();
    $dateStr = $dateStr ?: date('Y-m-d');

    $stmt = $db->prepare("SELECT round_id FROM daily_schedule WHERE play_date = ?");
    $stmt->execute([$dateStr]);
    $existing = $stmt->fetch();

    if ($existing) {
        $roundId = $existing['round_id'];
    } else {
        // Pick the active round that has been used least recently (or never)
        $stmt = $db->query("
            SELECT r.id FROM rounds r
            LEFT JOIN daily_schedule d ON d.round_id = r.id
            WHERE r.is_active = 1
            GROUP BY r.id
            ORDER BY MAX(d.play_date) IS NULL DESC, MAX(d.play_date) ASC, r.id ASC
            LIMIT 1
        ");
        $row = $stmt->fetch();
        if (!$row) return null; // no active rounds yet
        $roundId = $row['id'];
        $ins = $db->prepare("INSERT INTO daily_schedule (play_date, round_id) VALUES (?, ?)");
        $ins->execute([$dateStr, $roundId]);
    }

    $stmt = $db->prepare("SELECT * FROM rounds WHERE id = ?");
    $stmt->execute([$roundId]);
    return $stmt->fetch();
}

// ---- TMDb proxy (keeps API key server-side) ----
function tmdb_search_movies($query) {
    $url = "https://api.themoviedb.org/3/search/movie?api_key=" . TMDB_API_KEY
         . "&query=" . urlencode($query) . "&include_adult=false";
    $ctx = stream_context_create(['http' => ['timeout' => 6]]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res === false) return ['results' => []];
    $data = json_decode($res, true);
    return $data ?: ['results' => []];
}
