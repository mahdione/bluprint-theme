(()=>{
'use strict';

const reducedMotion=window.matchMedia('(prefers-reduced-motion:reduce)');

const animateCount=element=>{
    const target=Number(element.dataset.searchCount)||0;
    const finalLabel=element.dataset.searchCountLabel||String(target);

    if(target<=0||reducedMotion.matches){
        element.textContent=finalLabel;
        return;
    }

    const duration=680;
    let start=null;

    const step=timestamp=>{
        if(start===null)start=timestamp;

        const progress=Math.min(1,(timestamp-start)/duration);
        const eased=1-Math.pow(1-progress,3);
        element.textContent=String(Math.round(target*eased));

        if(progress<1){
            window.requestAnimationFrame(step);
        }else{
            element.textContent=finalLabel;
        }
    };

    window.requestAnimationFrame(step);
};

const start=()=>{
    document.querySelectorAll('[data-search-count]').forEach(animateCount);
};

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',start,{once:true});
}else{
    start();
}
})();
