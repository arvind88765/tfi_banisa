const modeConfig = {
  audio: { min: 1, max: 10, accept: 'video/*,audio/*', label: 'Clip' },
  image: { min: 3, max: 3, accept: 'image/*', label: 'Image' },
  music: { min: 1, max: 1, accept: 'video/*,audio/*', label: 'Soundtrack clip' },
};

const slotsEl = document.getElementById('asset-slots');
const addBtn = document.getElementById('add-asset-btn');
const modeRadios = document.querySelectorAll('input[name="mode"]');

let slotCount = 0;

function currentMode() {
  return document.querySelector('input[name="mode"]:checked').value;
}

function resetSlots() {
  slotsEl.innerHTML = '';
  slotCount = 0;
  const cfg = modeConfig[currentMode()];
  for (let i = 0; i < cfg.min; i++) addSlot();
  updateAddBtn();
}

function addSlot() {
  const cfg = modeConfig[currentMode()];
  if (slotCount >= cfg.max) return;
  slotCount++;
  const row = document.createElement('div');
  row.className = 'asset-slot';
  row.innerHTML = `
    <span class="slot-num">${slotCount}.</span>
    <input type="file" name="assets[]" accept="${cfg.accept}" required>
    ${slotCount > cfg.min ? '<button type="button" class="remove-slot link-danger">remove</button>' : ''}
  `;
  slotsEl.appendChild(row);
  const removeBtn = row.querySelector('.remove-slot');
  if (removeBtn) {
    removeBtn.addEventListener('click', () => {
      row.remove();
      slotCount--;
      renumberSlots();
      updateAddBtn();
    });
  }
  updateAddBtn();
}

function renumberSlots() {
  [...slotsEl.querySelectorAll('.asset-slot')].forEach((row, i) => {
    row.querySelector('.slot-num').textContent = (i + 1) + '.';
  });
}

function updateAddBtn() {
  const cfg = modeConfig[currentMode()];
  addBtn.style.display = slotCount >= cfg.max ? 'none' : 'inline-block';
  addBtn.textContent = `+ Add another (${slotCount}/${cfg.max})`;
}

addBtn.addEventListener('click', addSlot);
modeRadios.forEach(r => r.addEventListener('change', resetSlots));
resetSlots();

// ---- TMDb fuzzy search for tagging the correct answer ----
const answerInput = document.getElementById('answer-search');
const answerResults = document.getElementById('answer-results');
const pickedBox = document.getElementById('answer-picked');
const pickedPoster = document.getElementById('picked-poster');
const pickedTitle = document.getElementById('picked-title');
const clearBtn = document.getElementById('clear-picked');

let debounce;
answerInput.addEventListener('input', () => {
  clearTimeout(debounce);
  const q = answerInput.value.trim();
  if (q.length < 2) { answerResults.innerHTML = ''; return; }
  debounce = setTimeout(async () => {
    const res = await fetch('tmdb_search.php?q=' + encodeURIComponent(q));
    const data = await res.json();
    renderAnswerResults(data.results || []);
  }, 250);
});

function renderAnswerResults(items) {
  if (!items.length) { answerResults.innerHTML = '<div class="dim" style="padding:10px;">No matches</div>'; return; }
  answerResults.innerHTML = items.map(m => `
    <div class="autocomplete-row" data-id="${m.id}" data-title="${escapeHtml(m.title)}" data-year="${m.year}" data-poster="${m.poster_path || ''}">
      ${m.poster_path ? `<img class="mini-poster" src="https://image.tmdb.org/t/p/w45${m.poster_path}">` : '<div class="mini-poster"></div>'}
      <span>${escapeHtml(m.title)} <span class="dim">(${m.year})</span></span>
    </div>
  `).join('');
  [...answerResults.querySelectorAll('.autocomplete-row')].forEach(row => {
    row.addEventListener('click', () => {
      document.getElementById('tmdb_id').value = row.dataset.id;
      document.getElementById('movie_title').value = row.dataset.title;
      document.getElementById('movie_year').value = row.dataset.year;
      document.getElementById('poster_path').value = row.dataset.poster;
      pickedTitle.textContent = `${row.dataset.title} (${row.dataset.year})`;
      pickedPoster.src = row.dataset.poster ? `https://image.tmdb.org/t/p/w92${row.dataset.poster}` : '';
      pickedBox.style.display = 'flex';
      answerInput.style.display = 'none';
      answerResults.innerHTML = '';
    });
  });
}

clearBtn.addEventListener('click', () => {
  pickedBox.style.display = 'none';
  answerInput.style.display = 'block';
  answerInput.value = '';
  document.getElementById('tmdb_id').value = '';
});

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
