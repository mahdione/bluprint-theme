document.addEventListener('DOMContentLoaded',()=>{
    const button=document.querySelector('.back-to-top');
    if(!button)return;
    let visible=false;
    const update=()=>{
        const next=window.scrollY>400;
        if(next===visible)return;
        visible=next;
        button.classList.toggle('is-visible',visible);
        button.setAttribute('aria-hidden',String(!visible));
        button.tabIndex=visible?0:-1;
    };
    window.addEventListener('scroll',update,{passive:true});
    update();
    button.addEventListener('click',()=>{
        window.scrollTo({top:0,behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth'});
    });
});