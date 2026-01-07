document.addEventListener("DOMContentLoaded",()=>{
 const t=document.querySelector("h1");
 t.onmouseover=()=>t.textContent="🔍 what is hidden?";
 t.onmouseout=()=>t.textContent="📄 Lost in Pages";
});
