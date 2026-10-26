const API='api.php?route=';let CSRF='';
async function api(path,options={}){const h={'Content-Type':'application/json',...(options.headers||{})};if(CSRF)h['X-CSRF-Token']=CSRF;const r=await fetch(API+path,{credentials:'same-origin',...options,headers:h});const j=await r.json();if(!r.ok)throw new Error(j.error||'Request failed');return j.data;}
function esc(v){return String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}function badge(s){return `<span class="badge ${String(s).toLowerCase().replace(/\s/g,'')}">${esc(s)}</span>`}function toast(m){const x=document.querySelector('.toastBox');x.innerHTML=`<div class="toast">${esc(m)}</div>`;setTimeout(()=>x.innerHTML='',2500);}
function sessionStatus(x){
  if(+x.cancelled===1) return 'Cancelled';
  const now=new Date();
  const start=new Date(`${x.session_date}T${String(x.start_time).slice(0,8)}`);
  const end=new Date(`${x.session_date}T${String(x.end_time).slice(0,8)}`);
  if(now>=start && now<=end) return 'Live';
  if(now<start) return 'Upcoming';
  return +x.closed===1 ? 'Completed' : 'Finished';
}
async function timetable(){
  const rows=await api('student/timetable');
  const body=document.querySelector('#studentTimetable tbody');
  if(!rows.length){body.innerHTML='<tr><td colspan="6">No classes are scheduled yet.</td></tr>';return;}
  body.innerHTML=rows.map(x=>`<tr><td>${esc(x.session_date)}</td><td><b>${esc(x.code)}</b><div class="secondary">${esc(x.title)}</div></td><td>${String(x.start_time).slice(0,5)}–${String(x.end_time).slice(0,5)}</td><td>${esc(x.venue_name)}</td><td>${esc(x.lecturer_name)}</td><td>${badge(sessionStatus(x))}</td></tr>`).join('');
}
async function load(){const me=await api('auth/me');CSRF=me.csrf;const name=`${me.user.first_name} ${me.user.last_name}`;document.querySelector('#topName').textContent=name;document.querySelector('#sideName').textContent=name;document.querySelector('#profileEmail').value=me.user.email||'';document.querySelector('#profilePhone').value=me.user.phone||'';const d=await api('student/dashboard');document.getElementById('sCourses').textContent=d.courses;document.getElementById('sPresent').textContent=d.today.Present;document.getElementById('sLate').textContent=d.today.Late;document.getElementById('sAbsent').textContent=d.today.Absent;document.getElementById('thresholdText').textContent=`Eligibility threshold: ${d.threshold}%`;await timetable();const c=await api('student/courses');document.querySelector('#courseTable tbody').innerHTML=c.map(x=>{const p=Number(x.progress_percentage||0);const note=x.occurred_sessions>0?`${x.attended} attended / ${x.occurred_sessions} class${x.occurred_sessions==1?'':'es'} occurred`:'No class has occurred yet';return `<tr><td><b>${esc(x.code)}</b><div class="secondary">${esc(x.title)}</div></td><td>${x.percentage}%<div class="secondary">${x.attended} attended · ${x.planned_sessions} planned · ${x.closed_sessions} closed</div></td><td><div class="progress"><div class="bar green" style="width:${p}%"></div></div><div class="secondary">${esc(note)} · ${p}% of planned</div></td><td>${badge(x.eligible?'Eligible':'Not Eligible')}</td></tr>`;}).join('');const h=await api('student/attendance');document.querySelector('#historyTable tbody').innerHTML=h.length?h.map(x=>`<tr><td>${esc(x.session_date)}</td><td>${esc(x.code)}</td><td>${String(x.start_time).slice(0,5)}</td><td>${badge(x.status)}</td><td>${esc(x.source)}</td></tr>`).join(''):'<tr><td colspan="5">No attendance records yet.</td></tr>';}
document.addEventListener('DOMContentLoaded',async()=>{try{await load();}catch(e){location.href='index.php';return;}document.querySelectorAll('.nav button[data-view]').forEach(b=>b.addEventListener('click',()=>{document.querySelectorAll('.view').forEach(x=>x.classList.remove('active'));document.getElementById(b.dataset.view).classList.add('active');}));document.querySelectorAll('[data-action="refresh"]').forEach(b=>b.addEventListener('click',()=>load().then(()=>toast('Refreshed')).catch(e=>toast(e.message))));document.querySelectorAll('[data-action="refreshTimetable"]').forEach(b=>b.addEventListener('click',()=>timetable().then(()=>toast('Timetable refreshed')).catch(e=>toast(e.message))));let autoRefreshBusy=false;
setInterval(async()=>{
  if(autoRefreshBusy) return;
  const active=document.querySelector('.view.active');
  if(!active) return;
  if(document.activeElement && /^(INPUT|SELECT|TEXTAREA)$/.test(document.activeElement.tagName)) return;
  autoRefreshBusy=true;
  try {
    if(active.id==='dashboard') {
      const d=await api('student/dashboard');
      document.getElementById('sCourses').textContent=d.courses;
      document.getElementById('sPresent').textContent=d.today.Present;
      document.getElementById('sLate').textContent=d.today.Late;
      document.getElementById('sAbsent').textContent=d.today.Absent;
      document.getElementById('thresholdText').textContent=`Eligibility threshold: ${d.threshold}%`;
      await timetable();
      const c=await api('student/courses');
      document.querySelector('#courseTable tbody').innerHTML=c.map(x=>{const p=Number(x.progress_percentage||0);const note=x.occurred_sessions>0?`${x.attended} attended / ${x.occurred_sessions} class${x.occurred_sessions==1?'':'es'} occurred`:'No class has occurred yet';return `<tr><td><b>${esc(x.code)}</b><div class="secondary">${esc(x.title)}</div></td><td>${x.percentage}%<div class="secondary">${x.attended} attended · ${x.planned_sessions} planned · ${x.closed_sessions} closed</div></td><td><div class="progress"><div class="bar green" style="width:${p}%"></div></div><div class="secondary">${esc(note)} · ${p}% of planned</div></td><td>${badge(x.eligible?'Eligible':'Not Eligible')}</td></tr>`;}).join('');
    } else if(active.id==='attendance') {
      const h=await api('student/attendance');
      document.querySelector('#historyTable tbody').innerHTML=h.length?h.map(x=>`<tr><td>${esc(x.session_date)}</td><td>${esc(x.code)}</td><td>${String(x.start_time).slice(0,5)}</td><td>${badge(x.status)}</td><td>${esc(x.source)}</td></tr>`).join(''):'<tr><td colspan="5">No attendance records yet.</td></tr>';
    }
  } catch(e) { /* Background refresh should not interrupt normal use. */ }
  finally { autoRefreshBusy=false; }
},2000);document.getElementById('profileForm').addEventListener('submit',async e=>{e.preventDefault();try{await api('profile/update',{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(e.target)))});toast('Profile saved');}catch(x){toast(x.message)}});document.getElementById('passwordForm').addEventListener('submit',async e=>{e.preventDefault();try{await api('profile/password',{method:'POST',body:JSON.stringify(Object.fromEntries(new FormData(e.target)))});e.target.reset();toast('Password updated');}catch(x){toast(x.message)}});document.querySelector('[data-logout]').addEventListener('click',async()=>{try{await api('auth/logout',{method:'POST',body:'{}'});}finally{location.href='index.php';}});});
