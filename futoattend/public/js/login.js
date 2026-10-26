document.addEventListener('DOMContentLoaded',()=>{
 const form=document.querySelector('#loginForm'), id=document.querySelector('#loginId'), pw=document.querySelector('#password'), msg=document.querySelector('#loginMsg');
 const demos={student:['1','password'],lecturer:['STAFF/CSC/001','password'],admin:['ADMIN001','password']};
 const API='api.php?route=';
 document.querySelectorAll('[data-demo]').forEach(b=>b.addEventListener('click',()=>{const d=demos[b.dataset.demo];if(d){id.value=d[0];pw.value=d[1];msg.textContent='Demo account loaded. Click Login to continue.';msg.className='ok';}}));
 document.querySelector('[data-showpw]')?.addEventListener('click',()=>pw.type=pw.type==='password'?'text':'password');
 form?.addEventListener('submit',async e=>{
   e.preventDefault(); msg.textContent='Signing in…'; msg.className='';
   try{
     const r=await fetch(API+'auth/login',{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify({login_id:id.value.trim(),password:pw.value})});
     const j=await r.json();
     if(!r.ok) throw new Error(j.error||'Login failed');
     const role=j.data.user.role; localStorage.setItem('attendance_role',role);
     location.href=role==='student'?'student.php':role==='lecturer'?'lecturer.php':'admin.php';
   }catch(err){msg.textContent=err.message;msg.className='error';}
 });
});