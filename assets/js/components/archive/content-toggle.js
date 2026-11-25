(()=>{
'use strict';

const body=document.querySelector('[data-archive-content]');
const button=document.querySelector('[data-archive-toggle]');

if(!body||!button)return;

const limit=180;

if(body.scrollHeight<=limit+10){
    button.hidden=true;
    return;
}

body.classList.add('is-collapsed');

button.addEventListener('click',()=>{
    const expanded=button.getAttribute('aria-expanded')==='true';
    const next=!expanded;

    body.classList.toggle('is-collapsed',!next);
    body.classList.toggle('is-expanded',next);
    button.setAttribute('aria-expanded',String(next));
    button.textContent=next?'نمایش کمتر':'ادامه مطلب';
});
})();