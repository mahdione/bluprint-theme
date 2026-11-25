(()=>{
'use strict';

const reducedMotion=window.matchMedia('(prefers-reduced-motion:reduce)');

const init=()=>{
    const page=document.querySelector('[data-error-page]');
    const visual=page?.querySelector('[data-error-visual]');
    if(!page||!visual)return;

    const coord=page.querySelector('[data-error-coord]');
    let frame=0;
    let pointerX=0;
    let pointerY=0;

    const render=()=>{
        frame=0;
        visual.style.setProperty('--pointer-x',pointerX.toFixed(3));
        visual.style.setProperty('--pointer-y',pointerY.toFixed(3));

        if(coord){
            coord.textContent=`X: ${(pointerX*100).toFixed(2)} / Y: ${(pointerY*100).toFixed(2)}`;
        }
    };

    const schedule=()=>{
        if(frame)return;
        frame=window.requestAnimationFrame(render);
    };

    if(!reducedMotion.matches){
        page.addEventListener('pointermove',event=>{
            const rect=visual.getBoundingClientRect();
            if(!rect.width||!rect.height)return;

            pointerX=Math.max(-1,Math.min(1,((event.clientX-rect.left)/rect.width)*2-1));
            pointerY=Math.max(-1,Math.min(1,((event.clientY-rect.top)/rect.height)*2-1));
            schedule();
        },{passive:true});

        page.addEventListener('pointerleave',()=>{
            pointerX=0;
            pointerY=0;
            schedule();
        });

        visual.addEventListener('click',()=>{
            if(reducedMotion.matches)return;

            visual.classList.remove('is-recalibrating');
            void visual.offsetWidth;
            visual.classList.add('is-recalibrating');

            window.setTimeout(()=>visual.classList.remove('is-recalibrating'),950);
        });
    }
};

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',init,{once:true});
}else{
    init();
}
})();
