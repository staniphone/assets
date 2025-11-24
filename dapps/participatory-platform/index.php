<?php
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IoCambioPozzuoli – Proponi, vota e candida</title>
  <meta name="description" content="Proponi idee, vota le priorità e segui i candidabili della community." />
  <style>
    :root{
      --blue:#1c68b7; --blue-600:#155799; --yellow:#ffd912; --ink:#0b1220; --muted:#6b7280;
      --bg:#f9fafb; --card:#fff; --ok:#10b981; --danger:#ef4444; --radius:16px; --shadow:0 10px 30px rgba(2,6,23,.08);
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:var(--bg);color:var(--ink)}
    a{color:inherit;text-decoration:none}
    header.hero{position:relative;overflow:hidden;text-align:center;padding:90px 20px 60px;background:radial-gradient(1200px 600px at 10% -10%, #e7f2ff 0%, transparent 60%),radial-gradient(1200px 600px at 90% 0%, #fff6c7 0%, transparent 60%),linear-gradient(180deg,#ffffff 0%, #f3f4f6 100%)}
    h1.title{font-size:clamp(28px,4.5vw,56px);margin:0 0 12px}
    p.subtitle{font-size:clamp(16px,2vw,20px);margin:0 auto 22px;max-width:820px;color:#334155}
    .wrap{max-width:1150px;margin:0 auto;padding:20px}
    h2.section-title{text-align:center;color:var(--blue);font-size:clamp(22px,3vw,30px);margin:0 0 28px}
    .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px}
    .card{background:var(--card);padding:20px;border-radius:var(--radius);box-shadow:var(--shadow)}
    .card h3{margin:0 0 6px;color:var(--blue);font-size:18px}
    .card p{margin:0;color:#374151}
    .card.full{grid-column:span 12}
    .card.one-third{grid-column:span 4}
    .card.half{grid-column:span 6}
    @media (max-width:900px){.card.one-third,.card.half{grid-column:span 6}}
    @media (max-width:640px){.card.one-third,.card.half,.card.full{grid-column:span 12}}
    .nav{position:sticky;top:0;z-index:50;background:#fff;border-bottom:1px solid #e5e7eb;box-shadow:0 6px 30px rgba(0,0,0,.04)}
    .nav .wrap{display:flex;align-items:center;justify-content:space-between}
    .brand{display:flex;align-items:center;gap:12px;font-weight:800;letter-spacing:.3px}
    .brand svg{width:48px;height:48px;flex:none;filter:drop-shadow(0 4px 10px rgba(0,0,0,.06))}
    .nav .actions{display:flex;gap:8px;align-items:center}
    .btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:12px;font-weight:800;border:1px solid #e5e7eb;background:#fff;cursor:pointer}
    .btn.primary{background:var(--blue);color:#fff;border-color:var(--blue)}
    .btn.primary:hover{background:var(--blue-600)}
    .btn.ghost{border:1px solid #e5e7eb;background:#fff}
    .btn.link{padding:6px 8px;border:none}
    .badge{display:inline-block;padding:6px 10px;border-radius:999px;font-weight:700;background:#e0f2fe;color:#0c4a6e;font-size:13px}
    .meta{color:var(--muted);font-size:13px}
    label{display:block;font-weight:700;margin-bottom:6px;color:#0b1220}
    input,select,textarea{width:100%;padding:11px 12px;border:1px solid #e5e7eb;border-radius:10px;font-size:15px;background:#fff}
    textarea{resize:vertical;min-height:110px}
    .chips{display:flex;gap:8px;flex-wrap:wrap}
    .chip{padding:6px 10px;background:#eef2f7;color:#0f172a;border-radius:999px;font-size:12px}
    .votes{display:inline-flex;align-items:center;gap:8px;margin-top:4px}
    .btn-like{border:1px solid #e5e7eb;background:#fff;border-radius:999px;padding:8px 12px;cursor:pointer;font-weight:700;transition:all 0.2s ease}
    .btn-like:hover{transform:translateY(-2px);box-shadow:0 4px 8px rgba(0,0,0,0.1)}
    .idea-card{display:flex;flex-direction:column;gap:8px}
    .carousel{position:relative;display:grid;grid-template-columns:repeat(12,1fr);gap:16px;align-items:stretch}
    .carousel-card{grid-column:span 12}
    .carousel-card .card{height:100%;display:flex;flex-direction:column}
    .carousel-controls{display:flex;justify-content:center;gap:10px;margin-top:12px}
    .stat{display:flex;flex-direction:column;align-items:center}
    .stat strong{font-size:28px;color:var(--blue)}
    .activity{background:#f0f9ff;border-left:4px solid var(--blue);padding:12px 16px;border-radius:12px;margin:16px 0}
    .toast{position:fixed;bottom:18px;right:18px;background:#0b1220;color:#fff;padding:12px 16px;border-radius:10px;box-shadow:var(--shadow);opacity:0;transform:translateY(10px);transition:.3s}
    .toast.show{opacity:1;transform:translateY(0)}
    .table{width:100%;border-collapse:collapse;background:var(--card);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow)}
    .table th,.table td{padding:12px 14px;text-align:left;border-bottom:1px solid #f3f4f6}
    .table th{background:#f8fafc;color:#334155;font-size:14px}
    .modal{position:fixed;inset:0;background:rgba(0,0,0,.4);display:none;align-items:center;justify-content:center;padding:20px;z-index:70}
    .modal.open{display:flex}
    .modal .box{background:#fff;border-radius:14px;box-shadow:var(--shadow);padding:22px;max-width:420px;width:100%}
    @media(max-width:820px){
      #ideaForm .grid{grid-template-columns:repeat(6,1fr) !important}
      #ideaForm .half{grid-column:span 6 !important}
    }
    @media(max-width:620px){
      #ideaForm .grid{grid-template-columns:repeat(1,1fr) !important}
      #ideaForm .half{grid-column:span 1 !important}
    }
  </style>
</head>
<body>
  <nav class="nav" role="navigation" aria-label="Principale">
    <div class="wrap">
      <a href="#top" class="brand" aria-label="IoCambioPozzuoli">
        <svg width="64" height="64" viewBox="0 0 100 100" aria-hidden="true">
          <circle cx="50" cy="50" r="48" fill="var(--yellow)"/>
          <circle cx="50" cy="50" r="34" fill="var(--blue)"/>
          <path d="M38 52l6.5 8 17-19" fill="none" stroke="#fff" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>IoCambioPozzuoli</span>
      </a>
      <div class="actions">
        <a class="btn ghost" href="#idee">Idee</a>
        <a class="btn ghost" href="#candidabili">Candidabili</a>
        <a class="btn primary" href="#partecipa">Partecipa</a>
      </div>
    </div>
  </nav>

  <header class="hero" id="top">
    <p class="badge">Fase: raccolta + voto</p>
    <h1 class="title">Metti un <span style="color:var(--blue)">check</span> sul futuro di Pozzuoli</h1>
    <p class="subtitle">Proponi la tua idea, sostieni quelle che ti piacciono e segui un processo trasparente e pubblico. #IoCambioPozzuoli</p>
    <div style="display:flex;justify-content:center;gap:30px;flex-wrap:wrap;margin:18px 0;">
      <div class="stat"><strong id="statIdeas">0</strong><span>Idee proposte</span></div>
      <div class="stat"><strong id="statVotes">0</strong><span>Voti espressi</span></div>
      <div class="stat"><strong id="statUsers">0</strong><span>Partecipanti</span></div>
    </div>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a class="btn primary" href="#partecipa">🚀 Invia una proposta</a>
      <a class="btn ghost" href="#come-funziona">Come funziona</a>
      <a class="btn ghost" href="#candidabili">Diventa candidato</a>
    </div>
    <div style="color:var(--muted);margin-top:8px;font-size:14px">Dati minimi · Privacy first · Processo aperto</div>
  </header>

  <main id="main">
    <section id="come-funziona">
      <div class="wrap">
        <h2 class="section-title">Come funziona</h2>
        <div class="grid" role="list">
          <article class="card one-third" role="listitem"><h3>💡 Proponi</h3><p>Segnala un problema o lancia un progetto per quartiere o per tema.</p></article>
          <article class="card one-third" role="listitem"><h3>❤️ Vota</h3><p>Per votare serve un login leggero via email. 1 persona = 1 voto.</p></article>
          <article class="card one-third" role="listitem"><h3>🚀 Cambiamo</h3><p>Le idee più solide entrano nell'agenda e vengono monitorate pubblicamente.</p></article>
        </div>
      </div>
    </section>

    <section id="idee">
      <div class="wrap">
        <h2 class="section-title">Idee in evidenza</h2>
        <div class="activity" id="activityFeed">Attività in caricamento…</div>
        <div id="ideaCarousel" class="carousel" aria-live="polite" aria-busy="false"></div>
        <div class="carousel-controls">
          <button class="btn ghost" id="prevIdea" aria-label="Idea precedente">⬅️</button>
          <button class="btn ghost" id="nextIdea" aria-label="Idea successiva">➡️</button>
          <button class="btn ghost" id="shuffleIdea" aria-label="Idea casuale">🔀</button>
          <button class="btn ghost" id="simulateBtn">Simula nuova attività</button>
        </div>
      </div>
    </section>

    <section id="candidabili">
      <div class="wrap">
        <h2 class="section-title">Candidabili della comunità</h2>
        <p class="meta" style="text-align:center;max-width:780px;margin:0 auto 18px">Persone che si distinguono per idee popolari e contributo costante. Profili attivi e verificati.</p>
        <div id="candidates" class="grid" aria-live="polite"></div>
      </div>
    </section>

    <section id="partecipa">
      <div class="wrap">
        <h2 class="section-title">Partecipa ora</h2>
        <div class="grid">
          <div class="card full">
            <form id="ideaForm" novalidate>
              <h3 style="margin-top:0">Invia una proposta</h3>
              <div class="grid" style="grid-template-columns:repeat(12,1fr); gap:12px;">
                <div class="half" style="grid-column:span 6;">
                  <label for="title">Titolo</label>
                  <input id="title" name="title" required maxlength="120" placeholder="Es. Piste ciclabili sicure" />
                </div>
                <div class="half" style="grid-column:span 6;">
                  <label for="theme">Tema</label>
                  <select id="theme" name="theme">
                    <option>Mobilità</option>
                    <option>Ambiente</option>
                    <option>Scuola</option>
                    <option>Welfare</option>
                    <option>Sport e Cultura</option>
                    <option>Commercio</option>
                    <option>Altro</option>
                  </select>
                </div>
              </div>
              <div class="field">
                <label for="desc">Descrizione</label>
                <textarea id="desc" name="desc" rows="4" required maxlength="800" placeholder="Dettaglia benefici, costi e tempi"></textarea>
              </div>
              <div class="grid" style="grid-template-columns:repeat(12,1fr); gap:12px;">
                <div class="half" style="grid-column:span 6;">
                  <label for="district">Quartiere</label>
                  <select id="district" name="district" required>
                    <option value="">Seleziona…</option>
                    <option>Centro Storico (Rione Terra)</option>
                    <option>Via Napoli</option>
                    <option>Monterusciello</option>
                    <option>Toiano</option>
                    <option>Arco Felice</option>
                    <option>Lucrino</option>
                    <option>Licola</option>
                    <option>Altro</option>
                  </select>
                </div>
                <div class="half" style="grid-column:span 6;display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:end;">
                  <div>
                    <label for="author">Nome e cognome (opzionale)</label>
                    <input id="author" name="author" placeholder="Nome cognome" />
                  </div>
                  <div>
                    <label for="email">Email (per candidatura)</label>
                    <input id="email" name="email" placeholder="email@example.it" />
                  </div>
                </div>
              </div>
              <div class="field" style="display:flex; gap:10px; align-items:center;">
                <input type="checkbox" id="candidate" />
                <label for="candidate">Voglio essere tra i candidabili (richiede email e almeno 10 voti reali).</label>
              </div>
              <button class="btn primary" type="submit" id="submitBtn">Invia proposta</button>
              <p id="formMsg" class="meta"></p>
            </form>
          </div>
        </div>
      </div>
    </section>

    <section id="gamification">
      <div class="wrap">
        <h2 class="section-title">Sfida il tuo quartiere</h2>
        <table class="table" aria-label="Classifica quartieri per partecipazione">
          <thead><tr><th>#</th><th>Quartiere</th><th>Punteggio partecipazione</th></tr></thead>
          <tbody id="boardBody">
            <tr><td>1</td><td>Monterusciello</td><td>1240</td></tr>
            <tr><td>2</td><td>Centro Storico</td><td>1030</td></tr>
            <tr><td>3</td><td>Arco Felice</td><td>820</td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer style="background:#0b1220;color:#e5e7eb;padding:40px 20px;">
    <div class="wrap">
      <p><strong>IoCambioPozzuoli</strong> – movimento civico aperto. Scrivici: <a href="mailto:info@iocambiopozzuoli.it" style="color:#fde68a">info@iocambiopozzuoli.it</a></p>
      <p id="privacy">Privacy: trattiamo solo i dati necessari per le proposte e la candidatura.</p>
      <p>© <span id="year"></span> IoCambioPozzuoli. Tutti i diritti riservati.</p>
    </div>
  </footer>

  <div id="toast" class="toast" role="status">Operazione completata</div>

  <div id="loginModal" class="modal" aria-hidden="true" aria-modal="true" role="dialog">
    <div class="box">
      <h3 style="margin:0 0 10px">Accedi con email</h3>
      <p style="margin:0 0 12px;color:#334155">Inserisci la tua email: ti invieremo un link per accedere (niente password).</p>
      <div class="field"><label for="loginEmail">Email</label><input id="loginEmail" type="email" placeholder="nome@email.it"></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button id="loginCancel" class="btn ghost" type="button">Annulla</button>
        <button id="loginSend" class="btn primary" type="button">Invia link</button>
      </div>
      <p id="loginMsg" style="margin:10px 0 0;color:#64748b"></p>
    </div>
  </div>

<script>
const api = (action, options = {}) => fetch(`api.php?action=${action}`, {
  headers: { 'Content-Type': 'application/json' },
  credentials: 'same-origin',
  ...options,
}).then(res => res.json());

const toast = (msg) => {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 1800);
};

let carouselIdeas = [];
let carouselIndex = 0;
let carouselTimer;

function randomIndex(max, exclude) {
  if (max <= 1) return 0;
  let idx = Math.floor(Math.random() * max);
  if (idx === exclude) {
    idx = (idx + 1) % max;
  }
  return idx;
}

function renderIdea(idea) {
  const wrap = document.getElementById('ideaCarousel');
  wrap.setAttribute('aria-busy','true');
  if (!idea) {
    wrap.innerHTML = '<p class="meta" style="grid-column:span 12">Nessuna idea ancora: apri il back-office per pubblicare la prima.</p>';
    wrap.setAttribute('aria-busy','false');
    return;
  }

  wrap.innerHTML = '';
  const slot = document.createElement('div');
  slot.className = 'carousel-card';
  slot.innerHTML = `
    <article class="card idea-card">
      <div class="chips"><span class="chip">${idea.theme || 'Tema libero'}</span><span class="chip">${idea.district}</span></div>
      <h3>${idea.title}</h3>
      <p>${idea.description}</p>
      <p class="meta">Proposta da ${idea.author_name || 'Anonimo'} • <span class="vote-count">${idea.votes_count || 0}</span> voti • ${idea.comments_count || 0} commenti</p>
      <div class="votes">
        <button class="btn-like" data-id="${idea.id}" aria-label="Vota">👍 Vota</button>
        <button class="btn ghost" data-comments="${idea.id}">💬 Commenti</button>
      </div>
      <details id="comments-${idea.id}" style="margin-top:8px;">
        <summary>Commenti</summary>
        <div class="meta">Caricamento…</div>
      </details>
    </article>
  `;

  wrap.appendChild(slot);

  const voteBtn = slot.querySelector('[data-id]');
  const voteCount = slot.querySelector('.vote-count');
  voteBtn.addEventListener('click', async () => {
    await api('vote', { method: 'POST', body: JSON.stringify({ idea_id: idea.id }) });
    const current = parseInt(voteCount.textContent, 10) || 0;
    voteCount.textContent = current + 1;
    idea.votes_count = current + 1;
    carouselIdeas[carouselIndex] = idea;
    toast('Voto registrato');
    loadStats();
    loadCandidates();
  });

  slot.querySelector('[data-comments]').addEventListener('click', () => loadComments(idea.id));
  wrap.setAttribute('aria-busy','false');
}

function startCarousel() {
  clearInterval(carouselTimer);
  carouselTimer = setInterval(() => {
    if (!carouselIdeas.length) return;
    carouselIndex = randomIndex(carouselIdeas.length, carouselIndex);
    renderIdea(carouselIdeas[carouselIndex]);
  }, 8000);
}

async function loadIdeas() {
  const wrap = document.getElementById('ideaCarousel');
  wrap.setAttribute('aria-busy','true');
  wrap.innerHTML = '<p class="meta" style="grid-column:span 12">Caricamento in corso…</p>';
  const ideas = await api('ideas');
  if (!ideas.length) {
    carouselIdeas = [];
    renderIdea(null);
    return;
  }
  carouselIdeas = [...ideas].sort(() => Math.random() - 0.5);
  carouselIndex = 0;
  renderIdea(carouselIdeas[carouselIndex]);
  startCarousel();
}

async function loadComments(ideaId) {
  const wrapper = document.getElementById(`comments-${ideaId}`);
  const target = wrapper.querySelector('div');
  const comments = await api('comments&idea_id=' + ideaId);
  if (!comments.length) {
    target.innerHTML = '<p class="meta">Nessun commento. Scrivi tu il primo!</p>';
  } else {
    target.innerHTML = comments.map(c => `<p><strong>${c.author_name || 'Anonimo'}:</strong> ${c.body}</p>`).join('');
  }
  const form = document.createElement('form');
  form.innerHTML = `
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:8px;">
      <input name="author" placeholder="Nome" />
      <input name="body" placeholder="Commento rapido" required />
    </div>
    <button class="btn" style="margin-top:6px;">Pubblica</button>
  `;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(form);
    await api('comment', { method: 'POST', body: JSON.stringify({ idea_id: ideaId, author: data.get('author'), body: data.get('body') }) });
    toast('Commento aggiunto');
    loadComments(ideaId);
    loadStats();
  });
  target.appendChild(form);
}

async function loadCandidates() {
  const list = await api('candidates');
  const grid = document.getElementById('candidates');
  if (!list.length) {
    grid.innerHTML = '<p class="meta" style="grid-column:span 12">Nessun candidato visibile. Chiedi di essere incluso e raccogli 10 voti reali.</p>';
    return;
  }
  grid.innerHTML = '';
  list.forEach((c, idx) => {
    const card = document.createElement('article');
    card.className = 'card';
    card.style.gridColumn = 'span 4';
    card.innerHTML = `
      <div class="chips"><span class="chip">#${idx + 1}</span><span class="chip">${c.district || 'Pozzuoli'}</span></div>
      <h3>${c.name}</h3>
      <p class="meta">${c.role}</p>
      <p>${c.bio}</p>
      <p class="meta">${c.ideas} idee • ${c.votes} voti</p>
    `;
    grid.appendChild(card);
  });
}

async function loadStats() {
  const activity = await api('activity');
  const { stats, recent_activity } = activity;
  document.getElementById('statIdeas').textContent = stats.total_ideas;
  document.getElementById('statVotes').textContent = stats.total_votes;
  document.getElementById('statUsers').textContent = stats.active_users;
  document.getElementById('activityFeed').textContent = recent_activity;
}

async function submitIdea(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  const msg = document.getElementById('formMsg');
  btn.disabled = true;
  msg.textContent = '';

  const payload = {
    title: document.getElementById('title').value,
    desc: document.getElementById('desc').value,
    district: document.getElementById('district').value,
    theme: document.getElementById('theme').value,
    author: document.getElementById('author').value,
    author_email: document.getElementById('email').value,
    candidate_opt_in: document.getElementById('candidate').checked,
  };

  const res = await api('ideas', { method: 'POST', body: JSON.stringify(payload) });
  if (res.error) {
    msg.textContent = res.error;
    msg.style.color = 'var(--danger)';
  } else {
    msg.textContent = 'Idea inviata e visibile in lista!';
    msg.style.color = 'var(--ok)';
    document.getElementById('ideaForm').reset();
    loadIdeas();
    loadStats();
    loadCandidates();
  }
  btn.disabled = false;
}

async function simulate() {
  await api('simulate', { method: 'POST' });
  loadIdeas();
  loadStats();
  loadCandidates();
}

function bootstrap() {
  document.getElementById('year').textContent = new Date().getFullYear();
  loadIdeas();
  loadStats();
  loadCandidates();
  document.getElementById('ideaForm').addEventListener('submit', submitIdea);
  document.getElementById('simulateBtn').addEventListener('click', simulate);
  document.getElementById('prevIdea').addEventListener('click', () => {
    if (!carouselIdeas.length) return;
    carouselIndex = (carouselIndex - 1 + carouselIdeas.length) % carouselIdeas.length;
    renderIdea(carouselIdeas[carouselIndex]);
    startCarousel();
  });
  document.getElementById('nextIdea').addEventListener('click', () => {
    if (!carouselIdeas.length) return;
    carouselIndex = (carouselIndex + 1) % carouselIdeas.length;
    renderIdea(carouselIdeas[carouselIndex]);
    startCarousel();
  });
  document.getElementById('shuffleIdea').addEventListener('click', () => {
    if (!carouselIdeas.length) return;
    carouselIndex = randomIndex(carouselIdeas.length, carouselIndex);
    renderIdea(carouselIdeas[carouselIndex]);
    startCarousel();
  });
  document.getElementById('loginCancel').addEventListener('click', ()=> document.getElementById('loginModal').classList.remove('open'));
  document.getElementById('loginSend').addEventListener('click', ()=>{
    const email = (document.getElementById('loginEmail').value||'').trim();
    const msg = document.getElementById('loginMsg');
    if(!email){ msg.textContent = 'Email non valida'; msg.style.color = 'var(--danger)'; return; }
    msg.textContent = 'Link di accesso inviato!'; msg.style.color = 'var(--ok)';
  });
  setInterval(simulate, 15000);
  setInterval(loadStats, 12000);
}

document.addEventListener('DOMContentLoaded', bootstrap);
</script>
</body>
</html>
