<?php
// Front-end demo for the participatory platform (PHP + SQLite/MySQL backend)
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pozzuoli Partecipa – Idee, voti e candidature civiche</title>
  <style>
    :root {
      --blue: #1c68b7;
      --yellow: #ffd912;
      --card: #fff;
      --ink: #0b1220;
      --muted: #64748b;
      --ok: #0ea5e9;
      --shadow: 0 10px 30px rgba(11, 18, 32, 0.08);
      --radius: 14px;
    }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', system-ui, -apple-system, sans-serif; background: #f8fafc; color: var(--ink); }
    a { color: inherit; text-decoration: none; }
    header { background: linear-gradient(135deg, #f1f5f9 0%, #e0f2fe 50%, #fff 100%); padding: 60px 20px 40px; text-align: center; }
    h1 { margin: 0 0 12px; font-size: clamp(26px, 4vw, 44px); }
    p.lead { margin: 0 auto 18px; max-width: 820px; color: #334155; font-size: 18px; }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 20px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
    .card { background: var(--card); border-radius: var(--radius); padding: 18px; box-shadow: var(--shadow); }
    .badge { display: inline-block; background: #e0f2fe; color: #0c4a6e; padding: 6px 10px; border-radius: 999px; font-weight: 700; margin-right: 8px; }
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid #e2e8f0; background: #fff; font-weight: 700; cursor: pointer; }
    .btn.primary { background: var(--blue); color: #fff; border-color: var(--blue); }
    .btn[disabled] { opacity: .6; cursor: not-allowed; }
    form .field { margin-bottom: 12px; }
    form label { display: block; font-weight: 700; margin-bottom: 6px; }
    input, select, textarea { width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 15px; }
    textarea { resize: vertical; }
    .idea { display: grid; gap: 8px; }
    .chips { display: flex; gap: 8px; flex-wrap: wrap; }
    .chip { background: #e2e8f0; color: #0f172a; padding: 6px 10px; border-radius: 999px; font-size: 12px; }
    .meta { color: var(--muted); font-size: 13px; }
    .stat { text-align: center; }
    .stat strong { display: block; font-size: 26px; color: var(--blue); }
    .toast { position: fixed; right: 14px; bottom: 14px; background: var(--ink); color: #fff; padding: 10px 14px; border-radius: 10px; opacity: 0; transform: translateY(8px); transition: .3s; box-shadow: var(--shadow); }
    .toast.show { opacity: 1; transform: translateY(0); }
    .activity { background: #ecfeff; border: 1px solid #cffafe; padding: 12px; border-radius: 12px; margin-bottom: 16px; }
    details summary { cursor: pointer; font-weight: 700; }
  </style>
</head>
<body>
  <header>
    <p class="badge">Fase: raccolta + voto</p>
    <h1>Dai energia alle idee del tuo quartiere</h1>
    <p class="lead">Proponi soluzioni, vota quelle degli altri e diventa candidabile dopo 10 idee votate dal pubblico reale. Il motore civico simula attività costante per non far fermare la partecipazione.</p>
    <div style="display:flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
      <a class="btn primary" href="#partecipa">Invia una proposta</a>
      <a class="btn" href="#idee">Sfoglia idee</a>
      <a class="btn" href="#candidabili">Scopri i candidabili</a>
    </div>
  </header>

  <main class="wrap">
    <section aria-label="Statistiche" class="grid" id="stats">
      <div class="card stat"><strong id="statIdeas">0</strong><span>Idee</span></div>
      <div class="card stat"><strong id="statVotes">0</strong><span>Voti</span></div>
      <div class="card stat"><strong id="statUsers">0</strong><span>Partecipanti</span></div>
    </section>

    <section id="activity" style="margin-top: 30px;">
      <div class="activity" id="activityFeed">Attività in caricamento…</div>
    </section>

    <section id="idee">
      <h2>Idee pubblicate</h2>
      <div id="ideasGrid" class="grid" aria-live="polite"></div>
      <div style="margin-top:12px; display:flex; gap:8px;">
        <button class="btn" id="reloadIdeas">Aggiorna</button>
        <button class="btn" id="simulateBtn">Simula nuova attività</button>
      </div>
    </section>

    <section id="candidabili">
      <h2>Candidabili della community</h2>
      <p class="meta">Chi ha almeno 10 voti complessivi sulle proprie idee o ha chiesto di candidarsi.</p>
      <div id="candidates" class="grid"></div>
    </section>

    <section id="partecipa">
      <h2>Inserisci un'idea</h2>
      <div class="card">
        <form id="ideaForm">
          <div class="field">
            <label for="title">Titolo</label>
            <input id="title" name="title" required maxlength="120" placeholder="Es. Piste ciclabili sicure" />
          </div>
          <div class="field">
            <label for="desc">Descrizione</label>
            <textarea id="desc" name="desc" rows="4" required maxlength="800"></textarea>
          </div>
          <div class="field">
            <label for="district">Quartiere</label>
            <select id="district" name="district" required>
              <option value="">Seleziona…</option>
              <option>Centro Storico</option>
              <option>Monterusciello</option>
              <option>Toiano</option>
              <option>Arco Felice</option>
              <option>Lucrino</option>
              <option>Licola</option>
              <option>Altro</option>
            </select>
          </div>
          <div class="field">
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
          <div class="field">
            <label>Firma (opzionale)</label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
              <input id="author" name="author" placeholder="Nome e cognome" />
              <input id="email" name="email" placeholder="Email (per candidatura)" />
            </div>
          </div>
          <div class="field" style="display:flex; gap:8px; align-items:center;">
            <input type="checkbox" id="candidate" />
            <label for="candidate">Voglio essere tra i candidabili (richiede email e almeno 10 voti reali).</label>
          </div>
          <button class="btn primary" type="submit" id="submitBtn">Invia proposta</button>
          <p id="formMsg" class="meta"></p>
        </form>
      </div>
    </section>
  </main>

  <div id="toast" class="toast" role="status">Operazione completata</div>

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

async function loadIdeas() {
  const grid = document.getElementById('ideasGrid');
  grid.innerHTML = '<p class="meta">Caricamento in corso…</p>';
  const ideas = await api('ideas');
  if (!ideas.length) {
    grid.innerHTML = '<p class="meta">Nessuna idea ancora. Pubblica la prima!</p>';
    return;
  }
  grid.innerHTML = '';
  ideas.forEach(idea => {
    const card = document.createElement('article');
    card.className = 'card idea';
    card.innerHTML = `
      <div class="chips"><span class="chip">${idea.theme || 'Tema libero'}</span><span class="chip">${idea.district}</span></div>
      <h3>${idea.title}</h3>
      <p>${idea.description}</p>
      <p class="meta">Proposta da ${idea.author_name || 'Anonimo'} • ${idea.votes_count || 0} voti • ${idea.comments_count || 0} commenti</p>
      <div style="display:flex; gap:8px; align-items:center;">
        <button class="btn" data-id="${idea.id}" aria-label="Vota">👍 Vota</button>
        <button class="btn" data-comments="${idea.id}">💬 Commenta</button>
      </div>
      <details id="comments-${idea.id}" style="margin-top:8px;">
        <summary>Commenti</summary>
        <div class="meta">Caricamento…</div>
      </details>
    `;

    card.querySelector('[data-id]').addEventListener('click', async () => {
      await api('vote', { method: 'POST', body: JSON.stringify({ idea_id: idea.id }) });
      toast('Voto registrato');
      loadIdeas();
      loadStats();
      loadCandidates();
    });

    card.querySelector('[data-comments]').addEventListener('click', () => loadComments(idea.id));
    grid.appendChild(card);
  });
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
    grid.innerHTML = '<p class="meta">Nessun candidato visibile. Chiedi di essere incluso e raccogli 10 voti reali.</p>';
    return;
  }
  grid.innerHTML = '';
  list.forEach(c => {
    const card = document.createElement('article');
    card.className = 'card';
    card.innerHTML = `
      <h3>${c.name}</h3>
      <p class="meta">${c.district || 'Pozzuoli'} • ${c.role}</p>
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
    msg.style.color = 'red';
  } else {
    msg.textContent = 'Idea inviata e visibile in lista!';
    msg.style.color = 'green';
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
  loadIdeas();
  loadStats();
  loadCandidates();
  document.getElementById('ideaForm').addEventListener('submit', submitIdea);
  document.getElementById('reloadIdeas').addEventListener('click', loadIdeas);
  document.getElementById('simulateBtn').addEventListener('click', simulate);
  setInterval(simulate, 15000); // mantiene la piattaforma viva
  setInterval(loadStats, 12000);
}

document.addEventListener('DOMContentLoaded', bootstrap);
</script>
</body>
</html>
