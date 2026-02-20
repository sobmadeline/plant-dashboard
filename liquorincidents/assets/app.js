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
