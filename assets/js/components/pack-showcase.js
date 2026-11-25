(()=>{
'use strict';

const DURATION=190;
const EASE='cubic-bezier(.22,.68,0,1)';
const states=new WeakMap();

const getState=section=>{
    if(!states.has(section)){
        states.set(section,{
            token:0,
            timer:0,
            animations:new Set(),
            selected:'all'
        });
    }
    return states.get(section);
};

const cancelAnimations=state=>{
    clearTimeout(state.timer);
    state.animations.forEach(animation=>animation.cancel());
    state.animations.clear();
};

const animate=(state,element,keyframes,options)=>{
    if(!element.animate)return null;
    const animation=element.animate(keyframes,{
        duration:DURATION,
        easing:EASE,
        fill:'both',
        ...options
    });
    state.animations.add(animation);
    animation.onfinish=()=>state.animations.delete(animation);
    animation.oncancel=()=>state.animations.delete(animation);
    return animation;
};

const init=section=>{
    const filters=section.querySelector('[data-pack-filters]');
    const carousel=section.querySelector('[data-carousel]');
    if(!filters||!carousel||section.dataset.packFiltersReady)return;
    section.dataset.packFiltersReady='1';

    const buttons=[...filters.querySelectorAll('[data-pack-filter]')];
    const track=carousel.querySelector('[data-carousel-track]');
    const slides=[...carousel.querySelectorAll('[data-carousel-slide]')];
    if(!track||!slides.length)return;

    const state=getState(section);
    const reduced=window.matchMedia('(prefers-reduced-motion: reduce)');

    const matches=(slide,selected)=>{
        if(selected==='all')return true;
        return (slide.dataset.categoryIds||'').split(/\s+/).filter(Boolean).includes(selected);
    };

    const refresh=()=>{
        carousel.dispatchEvent(new CustomEvent('carousel:refresh'));
    };

    const applyFilter=selected=>{
        if(selected===state.selected)return;

        state.selected=selected;
        const token=++state.token;
        cancelAnimations(state);

        buttons.forEach(button=>{
            const active=button.dataset.packFilter===selected;
            button.classList.toggle('is-active',active);
            button.setAttribute('aria-pressed',String(active));
        });

        slides.forEach(slide=>{
            slide.getAnimations().forEach(animation=>animation.cancel());
            slide.style.removeProperty('opacity');
            slide.style.removeProperty('transform');
            slide.classList.remove('is-filter-hiding','is-filter-showing');
        });

        const currentlyVisible=slides.filter(slide=>!slide.hidden);
        const leaving=currentlyVisible.filter(slide=>!matches(slide,selected));
        const staying=currentlyVisible.filter(slide=>matches(slide,selected));

        if(reduced.matches){
            slides.forEach(slide=>slide.hidden=!matches(slide,selected));
            refresh();
            return;
        }

        if(!leaving.length){
            const entering=slides.filter(slide=>slide.hidden&&matches(slide,selected));
            const first=new Map(staying.map(slide=>[slide,slide.getBoundingClientRect()]));

            entering.forEach(slide=>slide.hidden=false);

            const last=new Map(staying.map(slide=>[slide,slide.getBoundingClientRect()]));

            staying.forEach(slide=>{
                const a=first.get(slide),b=last.get(slide);
                if(!a||!b)return;
                const dx=a.left-b.left,dy=a.top-b.top;
                if(Math.abs(dx)<1&&Math.abs(dy)<1)return;
                animate(state,slide,[
                    {transform:`translate(${dx}px,${dy}px)`},
                    {transform:'translate(0,0)'}
                ]);
            });

            entering.forEach((slide,index)=>{
                animate(state,slide,[
                    {opacity:0,transform:'translateY(14px)'},
                    {opacity:1,transform:'translateY(0)'}
                ],{delay:Math.min(index*35,140)});
            });

            refresh();
            return;
        }

        leaving.forEach((slide,index)=>{
            animate(state,slide,[
                {opacity:1,transform:'translateY(0)'},
                {opacity:0,transform:'translateY(-8px)'}
            ],{delay:Math.min(index*12,60)});
        });

        state.timer=window.setTimeout(()=>{
            if(token!==state.token)return;

            const first=new Map(
                staying.map(slide=>[slide,slide.getBoundingClientRect()])
            );

            leaving.forEach(slide=>slide.hidden=true);

            const entering=slides.filter(slide=>slide.hidden&&matches(slide,selected));
            entering.forEach(slide=>slide.hidden=false);

            const last=new Map(
                staying.map(slide=>[slide,slide.getBoundingClientRect()])
            );

            staying.forEach(slide=>{
                const a=first.get(slide),b=last.get(slide);
                if(!a||!b)return;

                const dx=a.left-b.left;
                const dy=a.top-b.top;

                if(Math.abs(dx)<1&&Math.abs(dy)<1)return;

                animate(state,slide,[
                    {transform:`translate(${dx}px,${dy}px)`},
                    {transform:'translate(0,0)'}
                ]);
            });

            entering.forEach((slide,index)=>{
                animate(state,slide,[
                    {opacity:0,transform:'translateY(14px)'},
                    {opacity:1,transform:'translateY(0)'}
                ],{delay:Math.min(index*35,140)});
            });

            refresh();
        },DURATION+70);
    };

    filters.addEventListener('click',event=>{
        const button=event.target.closest('[data-pack-filter]');
        if(!button||!filters.contains(button))return;
        applyFilter(button.dataset.packFilter);
    });
};

const initCart=()=>{
    if(document.documentElement.dataset.bpPackCartReady)return;
    document.documentElement.dataset.bpPackCartReady='1';

    let pending=null;
    let pendingTimer=0;

    const markAdded=button=>{
        if(!button)return;

        clearTimeout(button._bpAddedTimer);

        button.classList.add('is-added');
        button.setAttribute('aria-label','پک به سبد خرید اضافه شد');
        button.setAttribute('title','پک به سبد خرید اضافه شد');

        button._bpAddedTimer=window.setTimeout(()=>{
            button.classList.remove('is-added');
            button.setAttribute('aria-label',button.dataset.originalLabel||'افزودن به سبد خرید');
            button.setAttribute('title','افزودن به سبد خرید');
        },2000);
    };

    /* The Blueprint Commerce cart API already intercepts pack forms, performs
       the request and refreshes the header count and drawer. We must not
       preventDefault / disable / fetch here: doing so blocks the API handler
       (it skips disabled buttons) and leaves the header count stale.
       We only remember which button was used, then play the "added"
       animation once the cart count actually increases. */
    document.addEventListener('submit',event=>{
        const form=event.target.closest('.bp-pack-card__cart-form');
        if(!form)return;

        const button=event.submitter||form.querySelector('button[type="submit"]');
        if(!button)return;

        const count=document.querySelector('.header-cart__count');

        pending={
            button,
            count:Number(count?.textContent.trim())||0
        };

        clearTimeout(pendingTimer);
        pendingTimer=window.setTimeout(()=>{
            pending=null;
        },8000);
    });

    const count=document.querySelector('.header-cart__count');

    if(count&&window.MutationObserver){
        new MutationObserver(()=>{
            if(!pending)return;

            const next=Number(count.textContent.trim())||0;
            if(next<=pending.count)return;

            const button=pending.button;
            pending=null;
            clearTimeout(pendingTimer);
            markAdded(button);
        }).observe(count,{childList:true,characterData:true,subtree:true});
    }

    document.querySelectorAll('.bp-pack-card__cart-button').forEach(button=>{
        button.dataset.originalLabel=button.getAttribute('aria-label')||'افزودن به سبد خرید';
    });
};

const start=()=>{
    document.querySelectorAll('.bp-pack-showcase').forEach(init);
    initCart();
};

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',start,{once:true});
}else{
    start();
}
})();