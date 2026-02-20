function qs(sel){ return document.querySelector(sel); }
function qsa(sel){ return Array.from(document.querySelectorAll(sel)); }

async function api(url, opts={}){
  const res = await fetch(url, {credentials:'same-origin', ...opts});
  const ct = res.headers.get('content-type') || '';
  const body = ct.includes('application/json') ? await res.json().catch(()=>null) : await res.text().catch(()=>null);
  if (!res.ok){
    const msg = (body && body.error) ? body.error : (typeof body === 'string' ? body : `${res.status} ${res.statusText}`);
    throw new Error(msg);
  }
  return body;
}

function toast(msg){
  const t = qs('#toast');
  if (!t) { alert(msg); return; }
  t.textContent = msg;
  t.classList.remove('hidden');
  clearTimeout(window.__toast_t);
  window.__toast_t=setTimeout(()=>t.classList.add('hidden'), 2600);
}

async function guard(){
  try{
    const me = await api('/api/whoami.php', {cache:'no-store'});
    const el = qs('#whoami');
    if (el) el.textContent = me.user?.display_name || me.user?.name || 'Logged in';
  } catch(e){
    const next = encodeURIComponent(location.pathname + location.search + location.hash);
    location.href = `/login.html?next=${next}`;
  }
}

async function doLogout(){
  try { await fetch('/api/logout.php', {method:'POST', credentials:'same-origin'}); } catch(e){}
  location.href = '/login.html';
}

function yyyymmToday(){
  const d = new Date();
  const m = String(d.getMonth()+1).padStart(2,'0');
  return `${d.getFullYear()}-${m}`;
}


function escHtml(s){ return String(s).replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])); }

function initStaffPicker({searchInput, results, chips, hidden, endpoint}){
  const el = qs(searchInput);
  const elRes = qs(results);
  const elChips = qs(chips);
  const elHidden = qs(hidden);
  const btnClear = qs('#staff_clear');
  if(!el || !elRes || !elChips || !elHidden) return;

  let selected = [];
  try{ selected = JSON.parse(elHidden.value||'[]'); if(!Array.isArray(selected)) selected=[]; }catch{ selected=[]; }

  const renderChips = ()=>{
    elChips.innerHTML = '';
    selected.forEach(s=>{
      const b = document.createElement('button');
      b.type='button';
      b.className = 'px-3 py-1.5 rounded-full bg-slate-900 text-white text-sm font-bold hover:opacity-90';
      b.textContent = s.name;
      b.title = 'Click to remove';
      b.onclick = ()=>{
        selected = selected.filter(x=>x.id!==s.id);
        syncHidden();
        renderChips();
      };
      elChips.appendChild(b);
    });
  };

  const syncHidden = ()=>{
    elHidden.value = JSON.stringify(selected.map(x=>x.id));
  };

  const showResults = (items)=>{
    if(!items.length){ elRes.classList.add('hidden'); elRes.innerHTML=''; return; }
    elRes.classList.remove('hidden');
    elRes.innerHTML = items.map(it=>`
      <button type="button" data-id="${it.id}" data-name="${escHtml(it.name)}"
        class="w-full text-left px-3 py-2 hover:bg-slate-50 border-b last:border-b-0">
        <div class="font-bold">${escHtml(it.name)}</div>
        <div class="text-xs text-slate-500">${escHtml(it.role||'')}</div>
      </button>`).join('');
    qsa('button[data-id]', elRes).forEach(btn=>{
      btn.onclick = ()=>{
        const id = Number(btn.dataset.id);
        const name = btn.dataset.name || '';
        if(!selected.some(x=>x.id===id)){
          selected.push({id, name});
          syncHidden();
          renderChips();
        }
        el.value='';
        elRes.classList.add('hidden');
      };
    });
  };

  let t=null;
  el.addEventListener('input', ()=>{
    const q = el.value.trim();
    clearTimeout(t);
    if(q.length<1){ elRes.classList.add('hidden'); elRes.innerHTML=''; return; }
    t=setTimeout(async ()=>{
      try{
        const j = await api(`${endpoint}?q=${encodeURIComponent(q)}`);
        showResults((j.items||[]).slice(0,10));
      }catch(e){
        // don't toast here; just hide
        elRes.classList.add('hidden');
      }
    }, 180);
  });

  document.addEventListener('click', (ev)=>{
    if(!elRes.contains(ev.target) && ev.target!==el) elRes.classList.add('hidden');
  });

  if(btnClear){
    btnClear.onclick = ()=>{
      selected = [];
      syncHidden();
      renderChips();
      el.value='';
      elRes.classList.add('hidden');
    };
  }

  syncHidden();
  renderChips();
}
