let state = {
  roundId: null,
  mode: null,
  assets: [],
  maxAttempts: 0,
  currentAttempt: 1,
  dayNumber: 0,
  gameName: '',
  history: [], // 'correct' | 'wrong' per attempt used
  finished: false,
  answerTitle: '',
  answerYear: '',
};

const hintDisplay = document.getElementById('hint-display');
const attemptsGrid = document.getElementById('attempts-grid');
const guessInput = document.getElementById('guess-input');
const guessResults = document.getElementById('guess-results');
const resultPanel = document.getElementById('result-panel');
const dayLabel = document.getElementById('day-label');

const STORAGE_KEY_PREFIX = 'movieGuessState_';

async function init() {
  const res = await fetch('api/get_today.php');
  const data = await res.json();
  if (data.error) {
    hintDisplay.innerHTML = '<div class="dim" style="padding:30px;text-align:center;">No rounds are set up yet. Check back soon.</div>';
    return;
  }

  state.roundId = data.round_id;
  state.mode = data.mode;
  state.assets = data.assets.sort((a,b) => a.order - b.order);
  state.maxAttempts = data.max_attempts;
  state.dayNumber = data.day_number;
  state.gameName = data.game_name;

  dayLabel.textContent = `DAY ${state.dayNumber}`;

  // restore saved progress for today (so refresh doesn't reset the game)
  const saved = localStorage.getItem(STORAGE_KEY_PREFIX + state.dayNumber);
  if (saved) {
    const parsed = JSON.parse(saved);
    state.currentAttempt = parsed.currentAttempt;
    state.history = parsed.history;
    state.finished = parsed.finished;
    state.answerTitle = parsed.answerTitle || '';
    state.answerYear = parsed.answerYear || '';
  }

  buildAttemptsGrid();
  renderCurrentHint();
  guessInput.disabled = false;

  if (state.finished) {
    lockGame({ movie_title: state.answerTitle, movie_year: state.answerYear });
  }
}

function saveProgress() {
  localStorage.setItem(STORAGE_KEY_PREFIX + state.dayNumber, JSON.stringify({
    currentAttempt: state.currentAttempt,
    history: state.history,
    finished: state.finished,
    answerTitle: state.answerTitle,
    answerYear: state.answerYear,
  }));
}

function buildAttemptsGrid() {
  attemptsGrid.innerHTML = '';
  for (let i = 0; i < state.maxAttempts; i++) {
    const sq = document.createElement('div');
    sq.className = 'attempt-square pending';
    sq.dataset.index = i;
    attemptsGrid.appendChild(sq);
  }
  updateGridFromHistory();
}

function updateGridFromHistory() {
  const squares = attemptsGrid.querySelectorAll('.attempt-square');
  squares.forEach((sq, i) => {
    sq.className = 'attempt-square pending';
    if (state.history[i] === 'correct') sq.className = 'attempt-square correct';
    else if (state.history[i] === 'wrong') sq.className = 'attempt-square wrong';
  });
}

function currentAssetIndex() {
  const idx = Math.min(state.currentAttempt - 1, state.assets.length - 1);
  return idx;
}

function renderCurrentHint() {
  const asset = state.assets[currentAssetIndex()];
  if (!asset) return;

  if (asset.kind === 'image') {
    renderImageHints();
  } else {
    renderClipHint(asset);
  }
}

function renderImageHints() {
  // show all images revealed so far, cumulatively
  const revealCount = Math.min(state.currentAttempt, state.assets.length);
  hintDisplay.innerHTML = `<div class="image-hint-grid">` +
    state.assets.slice(0, revealCount).map(a => `<img class="hint-image" src="${a.path}">`).join('') +
    `</div>`;
}

function renderClipHint(asset) {
  const label = state.mode === 'music' ? 'Play soundtrack' : `Play clip ${asset.order}`;
  hintDisplay.innerHTML = `
    <div class="clip-hint">
      <button id="play-btn" class="play-btn">▶ ${label}</button>
      <video id="hidden-player" style="display:none;" preload="none"></video>
    </div>
  `;
  const btn = document.getElementById('play-btn');
  const player = document.getElementById('hidden-player');
  player.src = asset.path;
  btn.addEventListener('click', () => {
    player.currentTime = 0;
    player.play();
    btn.textContent = '❚❚ Playing…';
    player.onended = () => { btn.textContent = `▶ Replay clip ${asset.order}`; };
  });
}

// ---- Fuzzy guess search ----
let debounce;
guessInput.addEventListener('input', () => {
  clearTimeout(debounce);
  const q = guessInput.value.trim();
  if (q.length < 2) { guessResults.innerHTML = ''; return; }
  debounce = setTimeout(async () => {
    const res = await fetch('api/tmdb_search.php?q=' + encodeURIComponent(q));
    const data = await res.json();
    renderGuessResults(data.results || []);
  }, 250);
});

function renderGuessResults(items) {
  if (!items.length) { guessResults.innerHTML = ''; return; }
  guessResults.innerHTML = items.map(m => `
    <div class="autocomplete-row" data-id="${m.id}">
      ${m.poster_path ? `<img class="mini-poster" src="https://image.tmdb.org/t/p/w45${m.poster_path}">` : '<div class="mini-poster"></div>'}
      <span>${escapeHtml(m.title)} <span class="dim">(${m.year})</span></span>
    </div>
  `).join('');
  [...guessResults.querySelectorAll('.autocomplete-row')].forEach(row => {
    row.addEventListener('click', () => submitGuess(parseInt(row.dataset.id)));
  });
}

async function submitGuess(tmdbId) {
  if (state.finished) return;
  guessResults.innerHTML = '';
  guessInput.value = '';
  guessInput.disabled = true;

  const res = await fetch('api/check_guess.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ round_id: state.roundId, tmdb_id: tmdbId, attempt_number: state.currentAttempt }),
  });
  const data = await res.json();

  state.history[state.currentAttempt - 1] = data.correct ? 'correct' : 'wrong';
  updateGridFromHistory();

  if (data.game_over) {
    state.finished = true;
    state.answerTitle = data.movie_title || '';
    state.answerYear = data.movie_year || '';
    saveProgress();
    lockGame(data);
    return;
  }

  state.currentAttempt++;
  saveProgress();
  renderCurrentHint();
  guessInput.disabled = false;
  guessInput.focus();
}

function lockGame(data) {
  guessInput.disabled = true;
  guessInput.style.display = 'none';

  const won = state.history.includes('correct');
  resultPanel.style.display = 'block';
  resultPanel.innerHTML = `
    <div class="result-box ${won ? 'result-win' : 'result-lose'}">
      <div class="result-title">${won ? 'Correct! 🎬' : 'Out of tries'}</div>
      <div class="result-answer">${escapeHtml(data.movie_title || '')} ${data.movie_year ? '(' + data.movie_year + ')' : ''}</div>
      <button id="share-btn" class="btn btn-large" style="margin-top:16px;">Share result</button>
      <div id="share-copied" class="dim" style="margin-top:8px;display:none;">Copied to clipboard!</div>
    </div>
  `;
  document.getElementById('share-btn').addEventListener('click', () => shareResult(won));
}

function shareResult(won) {
  const used = state.history.filter(Boolean).length;
  const squares = state.history.map(h => h === 'correct' ? '🟩' : (h === 'wrong' ? '🟥' : '')).join('') +
    '⬛'.repeat(Math.max(0, state.maxAttempts - state.history.length));
  const scoreLine = won ? `${used}/${state.maxAttempts}` : `X/${state.maxAttempts}`;
  const text = `${state.gameName} Day ${state.dayNumber}: ${scoreLine}\n${squares}`;

  if (navigator.share) {
    navigator.share({ text }).catch(() => {});
  } else {
    navigator.clipboard.writeText(text).then(() => {
      document.getElementById('share-copied').style.display = 'block';
    });
  }
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

init();
