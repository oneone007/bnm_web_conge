<?php
// Simple PHP page rendering an HTML/JS app that queries the Flask API for expiring products
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Expiring Products</title>
  <style>
    :root {
      /* Dark theme (default) */
      --bg: #0f172a;
      --bg-2: #060b16;
      --panel: #111827;
      --panel-2: #0b1221;
      --text: #e5e7eb;
      --muted: #9ca3af;
      --brand: #22c55e;
      --warn: #f59e0b;
      --danger: #ef4444;
      --info: #3b82f6;
      --border: #334155;
      --chip: #1f2937;
      --header: rgba(8,13,26,0.8);
    }
    :root[data-theme="light"] {
      /* Light theme overrides */
      --bg: #f5f7fb;
      --bg-2: #ffffff;
      --panel: #ffffff;
      --panel-2: #f8fafc;
      --text: #0f172a;
      --muted: #475569;
      --brand: #16a34a;
      --warn: #d97706;
      --danger: #dc2626;
      --info: #2563eb;
      --border: #e5e7eb;
      --chip: #eef2f7;
      --header: rgba(255,255,255,0.85);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; padding: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      background: linear-gradient(180deg, var(--bg), var(--bg-2) 60%);
      color: var(--text);
    }
    header {
      padding: 16px 20px; border-bottom: 1px solid var(--border); position: sticky; top: 0; backdrop-filter: blur(6px);
      background: var(--header); z-index: 10; display:flex; align-items:center; justify-content:space-between;
    }
    h1 { font-size: 20px; margin: 0; letter-spacing: 0.3px; }
    .container { padding: 20px; max-width: 1400px; margin: 0 auto; }
    .filters { display: grid; grid-template-columns: repeat(6, minmax(140px, 1fr)); gap: 12px; background: var(--panel); padding: 14px; border-radius: 10px; border: 1px solid var(--border); }
    .field { display: flex; flex-direction: column; gap: 6px; }
    label { font-size: 12px; color: var(--muted); }
    select, input[type="text"], input[type="date"] {
      background: var(--panel-2); color: var(--text); border: 1px solid var(--border); border-radius: 8px; padding: 8px 10px; outline: none;
    }
    .btn {
      background: var(--info); color: white; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer; font-weight: 600;
    }
    .btn.secondary { background: #64748b; }
    .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 16px; }
    .card { background: var(--panel); border: 1px solid var(--border); border-radius: 10px; padding: 12px; }
    .card h3 { margin: 0 0 8px 0; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
    .pill { font-size: 11px; padding: 2px 8px; border-radius: 999px; background: var(--chip); color: var(--text); border: 1px solid var(--border); }
  .pill.expired { background: #fecaca; color: #000; border-color: #ef4444; font-weight: 800; }
  .pill.m1 { background: #fde68a; color: #000; border-color: #f59e0b; font-weight: 800; }
  .pill.m3 { background: #bfdbfe; color: #000; border-color: #3b82f6; font-weight: 800; }
  .pill.m6 { background: #bbf7d0; color: #000; border-color: #22c55e; font-weight: 800; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid var(--border); text-align: left; font-size: 13px; }
    th { color: var(--muted); font-weight: 600; }
    .section { margin-top: 22px; }
    .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .counts { color: var(--muted); font-size: 12px; }
    .muted { color: var(--muted); }
    .loading { padding: 10px; color: var(--muted); }
    .error { padding: 10px; color: #fecaca; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.35); border-radius: 8px; }
    .footer-space { height: 30px; }
  /* Scrollable category tables */
  .table-wrap { max-height: 60vh; overflow: auto; border: 1px solid var(--border); border-radius: 8px; }
  .table-wrap thead th { position: sticky; top: 0; background: var(--panel); z-index: 1; }
    @media (max-width: 1100px) { .filters { grid-template-columns: repeat(2, 1fr); } .summary { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .filters { grid-template-columns: 1fr; } .summary { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <header>
    <h1>Produits expirés et à expirer</h1>
  </header>
  <div class="container">
    <div class="filters">
      <div class="field">
        <label for="magasin">Magasin</label>
        <select id="magasin">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="field">
        <label for="emplacement">Emplacement</label>
        <select id="emplacement">
          <option value="">Tous</option>
        </select>
      </div>
      <div class="field">
        <label for="fournisseur">Fournisseur</label>
        <input type="text" id="fournisseur" placeholder="Prefixe fournisseur" />
      </div>
      <div class="field">
        <label for="refdate">Date de référence</label>
        <input type="date" id="refdate" />
      </div>
      <div class="field">
        <label for="months">Fenêtre</label>
        <select id="months">
          <option value="6">6 mois</option>
          <option value="3">3 mois</option>
          <option value="1">1 mois</option>
        </select>
      </div>
      <div class="field" style="align-self:end; display:flex; gap:8px; flex-wrap: wrap;">
        <button class="btn" id="btnSearch">Rechercher</button>
        <button class="btn secondary" id="btnReset">Réinitialiser</button>
        <button class="btn" style="background:#10b981;" id="btnDefault">Magasin par défaut</button>
  <button class="btn secondary" id="btnExcel">Télécharger Excel</button>
      </div>
    </div>

    <div class="summary">
      <div class="card"><h3><span class="pill expired">Expiré</span></h3><div id="sum-expired" class="muted">–</div></div>
      <div class="card"><h3><span class="pill m1">1 mois</span></h3><div id="sum-1m" class="muted">–</div></div>
      <div class="card"><h3><span class="pill m3">3 mois</span></h3><div id="sum-3m" class="muted">–</div></div>
      <div class="card"><h3><span class="pill m6">6 mois</span></h3><div id="sum-6m" class="muted">–</div></div>
    </div>

    <div id="status" class="loading" style="display:none;">Chargement…</div>
    <div id="error" class="error" style="display:none;"></div>

    <div id="sections"></div>
    <div class="footer-space"></div>
  </div>

  <script src="theme.js"></script>
  <script src="api_config.js"></script>
  <script>
    // Sync this page's CSS variables with the global theme (without touching theme.js)
    (function syncThemeForThisPage(){
      const docEl = document.documentElement;
      function applyFromState(){
        // Prefer localStorage('theme'), else infer from classes set by theme.js
        const t = localStorage.getItem('theme');
        const inferredDark = document.body.classList.contains('dark-mode') || docEl.classList.contains('dark');
        const isDark = t ? (t === 'dark') : inferredDark;
        if (isDark) {
          docEl.removeAttribute('data-theme'); // dark is default in CSS vars
        } else {
          docEl.setAttribute('data-theme', 'light');
        }
      }
      applyFromState();
      // React to global updates
      window.addEventListener('storage', (e) => { if (e.key === 'theme') applyFromState(); });
      window.addEventListener('themeChanged', applyFromState);
      // Fallback: observe class changes in case only classes are toggled
      const mo = new MutationObserver(applyFromState);
      mo.observe(docEl, { attributes: true, attributeFilter: ['class'] });
      mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });
    })();

    // Resolve API base via global API_CONFIG (auto-detects env), with a safe fallback
    const API_BASE = (window.API_CONFIG && typeof window.API_CONFIG.getBaseUrl === 'function')
      ? window.API_CONFIG.getBaseUrl()
      : 'http://bnm.ddns.net:5000';

    const el = (id) => document.getElementById(id);
    const magasinSel = el('magasin');
    const emplacementSel = el('emplacement');
    const fournisseurInp = el('fournisseur');
    const refDateInp = el('refdate');
    const monthsSel = el('months');
    const btnSearch = el('btnSearch');
    const btnReset = el('btnReset');
    const btnDefault = el('btnDefault');
  const btnExcel = el('btnExcel');
    const sections = el('sections');
    const statusBox = el('status');
  const errorBox = el('error');
    // Start in "default magasins" mode so data shows per default on load
    let useDefaultMagasins = true;

    // Build normalized default magasins list from exact provided values
    const DEFAULT_MAGASINS = [
      '1-Dépôt Principal',
      'HANGAR',
      '8-Dépot réserve',
      '88-Dépot Hangar réserve'
    ];
    const DEFAULT_MAGASINS_NORM = DEFAULT_MAGASINS.map(norm);

    // set default date today
    const today = new Date();
    const toYMD = (d) => d.toISOString().slice(0,10);
    refDateInp.value = toYMD(today);

    async function fetchJSON(url) {
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const txt = await res.text();
        if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText} — ${txt?.slice(0,300)}`);
        return txt ? JSON.parse(txt) : [];
      } catch (e) {
        throw e;
      }
    }

    function qs(params) {
      const q = new URLSearchParams();
      Object.entries(params).forEach(([k, v]) => { if (v !== undefined && v !== null && v !== '') q.set(k, v); });
      return q.toString();
    }

    function groupBy(arr, keyFn) {
      return arr.reduce((acc, item) => {
        const k = keyFn(item);
        (acc[k] ||= []).push(item);
        return acc;
      }, {});
    }

    function sum(arr, key) { return arr.reduce((a, b) => a + (Number(b[key]) || 0), 0); }
    function sumValue(arr) { return arr.reduce((a, b) => a + (Number(b.qty)||0) * (Number(b.price)||0), 0); }
      function formatPrice(v) {
        const n = Number(v || 0);
        return isNaN(n) ? '—' : n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

  function renderTables(data) {
      // categories: expired, 1mths, 3mths, 6mths
      const order = ['expired', '1mths', '3mths', '6mths'];
      const labels = { expired: 'Expiré', '1mths': '1 mois', '3mths': '3 mois', '6mths': '6 mois' };
      const pills = { expired: 'expired', '1mths': 'm1', '3mths': 'm3', '6mths': 'm6' };

      // Update summary
      const byCat = groupBy(data, x => x.category);
      const sExpired = byCat['expired'] || [];
      const s1 = byCat['1mths'] || [];
      const s3 = byCat['3mths'] || [];
      const s6 = byCat['6mths'] || [];
  el('sum-expired').innerText = `${sExpired.length} lignes · Qté ${sum(sExpired, 'qty')} · Val ${formatPrice(sumValue(sExpired))}`;
  el('sum-1m').innerText = `${s1.length} lignes · Qté ${sum(s1, 'qty')} · Val ${formatPrice(sumValue(s1))}`;
  el('sum-3m').innerText = `${s3.length} lignes · Qté ${sum(s3, 'qty')} · Val ${formatPrice(sumValue(s3))}`;
  el('sum-6m').innerText = `${s6.length} lignes · Qté ${sum(s6, 'qty')} · Val ${formatPrice(sumValue(s6))}`;

      sections.innerHTML = '';
      order.forEach(cat => {
        const rows = byCat[cat] || [];
        const wrapper = document.createElement('div');
        wrapper.className = 'section card';
        wrapper.innerHTML = `
          <div class="section-header">
            <h3><span class="pill ${pills[cat]}">${labels[cat]}</span></h3>
            <div class="counts">${rows.length} lignes · Qté ${sum(rows, 'qty')} · Val ${formatPrice(sumValue(rows))}</div>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Produit</th>
                  <th class="muted">Lot</th>
                  <th>Qté</th>
                  <th>Prix</th>
                  <th>Valeur</th>
                  <th>Date péremption</th>
                  <th>Magasin</th>
                  <th>Emplacement</th>
                  <th class="muted">Fournisseur</th>
                </tr>
              </thead>
              <tbody>
                ${rows.map(r => `
                  <tr>
                    <td>${escapeHtml(r.product_name ?? '')}</td>
                    <td class="muted">${escapeHtml(r.lot ?? '')}</td>
                    <td>${Number(r.qty || 0)}</td>
                    <td>${formatPrice(r.price)}</td>
                    <td>${formatPrice((Number(r.qty||0)) * (Number(r.price||0)))}</td>
                    <td>${escapeHtml(r.expire_date ?? '')}</td>
                    <td>${escapeHtml(r.magasin ?? '')}</td>
                    <td>${escapeHtml(r.emplacement ?? '')}</td>
                    <td class="muted">${escapeHtml(r.fournisseur ?? '')}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        `;
        sections.appendChild(wrapper);
      });
    }

    function escapeHtml(str) {
      return String(str).replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
    }

    async function loadMagasins() {
      const url = `${API_BASE}/fetch-magasins`;
      const list = await fetchJSON(url);
      const opts = ['<option value="">Tous</option>'].concat((Array.isArray(list)?list:[]).map(x => {
        const v = x.MAGASIN ?? x.magasin ?? x.value ?? '';
        const d = v;
        return `<option value="${encodeURIComponent(v)}">${escapeHtml(d)}</option>`;
      }));
      magasinSel.innerHTML = opts.join('');
    }

    async function loadEmplacements() {
      const params = { magasin: decodeURIComponent(magasinSel.value || '') };
      const url = `${API_BASE}/fetch-emplacements?${qs(params)}`;
      const list = await fetchJSON(url);
      const opts = ['<option value="">Tous</option>'].concat((Array.isArray(list)?list:[]).map(x => {
        const v = x.EMPLACEMENT ?? x.emplacement ?? x.value ?? '';
        const d = v;
        return `<option value="${encodeURIComponent(v)}">${escapeHtml(d)}</option>`;
      }));
      emplacementSel.innerHTML = opts.join('');
    }

    async function loadData() {
      errorBox.style.display = 'none'; errorBox.textContent = '';
      statusBox.style.display = 'block'; statusBox.textContent = 'Chargement…';
      sections.innerHTML = '';

      try {
        const params = {
          // If using default magasins mode, don't send a specific magasin (we filter client-side)
          magasin: useDefaultMagasins ? '' : decodeURIComponent(magasinSel.value || ''),
          emplacement: decodeURIComponent(emplacementSel.value || ''),
          fournisseur: fournisseurInp.value || '',
          date: refDateInp.value || toYMD(new Date()),
          within_months: monthsSel.value || '6',
        };
        const url = `${API_BASE}/expiring?${qs(params)}`;
        const data = await fetchJSON(url);
        let rows = Array.isArray(data) ? data.map(lowercaseKeys) : [];
        if (useDefaultMagasins) {
          rows = rows.filter(r => DEFAULT_MAGASINS_NORM.includes(norm(r.magasin)));
        }
  renderTables(rows);
  // Save last shown rows for export
  window.__lastRows = rows;
      } catch (err) {
        console.error(err);
        const msg = (err && err.message) ? String(err.message) : '';
        errorBox.textContent = 'Erreur lors du chargement des données. Vérifiez que le serveur API est actif. ' + msg;
        errorBox.style.display = 'block';
      } finally {
        statusBox.style.display = 'none';
      }
    }

    magasinSel.addEventListener('change', async () => { await loadEmplacements(); });
    btnSearch.addEventListener('click', () => { useDefaultMagasins = false; loadData(); });
    btnReset.addEventListener('click', async () => {
      magasinSel.value = '';
      await loadEmplacements();
      emplacementSel.value = '';
      fournisseurInp.value = '';
      refDateInp.value = toYMD(new Date());
      monthsSel.value = '6';
      useDefaultMagasins = false;
      await loadData();
    });
    btnDefault.addEventListener('click', async () => {
      // Activate default magasins filtering
      magasinSel.value = '';
      await loadEmplacements();
      useDefaultMagasins = true;
      await loadData();
    });
    btnExcel.addEventListener('click', () => {
      try { downloadExcel(); } catch (e) { console.error('Export failed', e); alert('Export Excel a échoué.'); }
    });

    // Initial load
    (async function init(){
      try {
        await loadMagasins();
        await loadEmplacements();
      } catch (e) { console.warn('Failed to load filters', e); }
      await loadData();
    })();

    function lowercaseKeys(obj) {
      if (!obj || typeof obj !== 'object') return obj;
      const out = {};
      for (const k of Object.keys(obj)) out[k.toLowerCase()] = obj[k];
      return out;
    }

    function norm(s) {
      try {
        return String(s || '').toLowerCase()
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .trim();
      } catch {
        return String(s || '').toLowerCase().trim();
      }
    }

    // Export current results to an Excel-compatible .xls (HTML workbook)
    function downloadExcel() {
      const rows = Array.isArray(window.__lastRows) ? window.__lastRows : [];
      if (!rows.length) { alert('Aucune donnée à exporter.'); return; }

      const order = ['expired', '1mths', '3mths', '6mths'];
      const labels = { expired: 'Expiré', '1mths': '1 mois', '3mths': '3 mois', '6mths': '6 mois' };

      const byCat = groupBy(rows, r => r.category);

      const esc = (s) => escapeHtml(s ?? '');
      const num = (v, digits = 2) => {
        const n = Number(v || 0);
        return isNaN(n) ? '' : n.toFixed(digits);
      };

      const tableFor = (cat) => {
        const rs = (byCat[cat] || []).slice().sort((a,b) => {
          const da = (a.expire_date||'');
          const db = (b.expire_date||'');
          return da.localeCompare(db) || String(a.product_name||'').localeCompare(String(b.product_name||''));
        });
        const tQty = rs.reduce((s, r) => s + (Number(r.qty)||0), 0);
        const tVal = rs.reduce((s, r) => s + (Number(r.qty)||0) * (Number(r.price)||0), 0);
        const header = `
          <tr>
            <th>Produit</th>
            <th>Lot</th>
            <th>Qté</th>
            <th>Prix</th>
            <th>Valeur</th>
            <th>Date péremption</th>
            <th>Magasin</th>
            <th>Emplacement</th>
            <th>Fournisseur</th>
          </tr>`;
        const body = rs.map(r => `
          <tr>
            <td>${esc(r.product_name)}</td>
            <td>${esc(r.lot)}</td>
            <td style="mso-number-format:'0';">${num(r.qty, 0)}</td>
            <td style="mso-number-format:'0.00';">${num(r.price)}</td>
            <td style="mso-number-format:'0.00';">${num((Number(r.qty)||0)*(Number(r.price)||0))}</td>
            <td>${esc(r.expire_date)}</td>
            <td>${esc(r.magasin)}</td>
            <td>${esc(r.emplacement)}</td>
            <td>${esc(r.fournisseur)}</td>
          </tr>`).join('');
        const footer = `
          <tr>
            <th colspan="2" style="text-align:right;">Totaux</th>
            <th style="mso-number-format:'0';">${num(tQty, 0)}</th>
            <th></th>
            <th style="mso-number-format:'0.00';">${num(tVal)}</th>
            <th colspan="4"></th>
          </tr>`;
        return `
          <h3 style="margin:12px 0 6px 0;">${labels[cat]} (${rs.length} lignes)</h3>
          <table border="1">
            <thead>${header}</thead>
            <tbody>${body}</tbody>
            <tfoot>${footer}</tfoot>
          </table>`;
      };

      const html = `<!DOCTYPE html>
        <html>
        <head>
          <meta charset="UTF-8" />
          <style>
            body{font-family:Segoe UI,Roboto,Arial,sans-serif}
            table{border-collapse:collapse;font-size:12px}
            th,td{padding:4px 6px}
            h3{font-size:14px}
          </style>
        </head>
        <body>
          <h2>Produits expirés et à expirer</h2>
          ${order.map(c => tableFor(c)).join('\n')}
        </body>
        </html>`;

      const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      const ref = refDateInp.value || new Date().toISOString().slice(0,10);
      a.href = url;
      a.download = `produits-expiration_${ref}.xls`;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => { URL.revokeObjectURL(url); a.remove(); }, 1000);
    }
  </script>
</body>
</html>
