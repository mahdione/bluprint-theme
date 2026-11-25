(()=>{
    'use strict';

    const init=()=>{
        const content=document.querySelector('[data-post-content]');
        const sidebar=document.querySelector('.bp-post__sidebar');
        if(!content||!sidebar)return;

        const details=sidebar.querySelector('[data-post-toc]');
        const output=sidebar.querySelector('[data-post-font-value]');
        const buttons=[...sidebar.querySelectorAll('[data-post-font-step]')];
        const storageKey='bluprint-post-font-scale-v1';
        const minimum=85;
        const maximum=140;
        const step=5;
        let scale=100;

        const scalableElements=[
            content,
            ...content.querySelectorAll('*')
        ].filter(element=>!element.matches('img,video,iframe,canvas,svg,source,br,hr,script,style'));
        const originalFontSizes=new Map();
        const baseFontSizes=new Map();

        scalableElements.forEach(element=>{
            originalFontSizes.set(element,{
                value:element.style.getPropertyValue('font-size'),
                priority:element.style.getPropertyPriority('font-size')
            });
        });

        const measureBaseFontSizes=()=>{
            content.style.setProperty('--bp-post-text-scale','1');

            scalableElements.forEach(element=>{
                const original=originalFontSizes.get(element);
                if(original.value){
                    element.style.setProperty('font-size',original.value,original.priority);
                }else{
                    element.style.removeProperty('font-size');
                }
            });

            scalableElements.forEach(element=>{
                const size=Number.parseFloat(window.getComputedStyle(element).fontSize);
                if(Number.isFinite(size))baseFontSizes.set(element,size);
            });
        };

        measureBaseFontSizes();

        const setScale=(value,save=true)=>{
            const next=minimum+Math.round((Math.max(minimum,Math.min(maximum,value))-minimum)/step)*step;
            scale=next;
            content.style.setProperty('--bp-post-text-scale',String(next/100));

            scalableElements.forEach(element=>{
                const baseSize=baseFontSizes.get(element);
                if(!baseSize)return;
                const original=originalFontSizes.get(element);
                element.style.setProperty('font-size',`${baseSize*next/100}px`,original.priority);
            });

            if(output)output.value=`${next}%`;

            buttons.forEach(button=>{
                const amount=Number(button.dataset.postFontStep);
                button.disabled=(amount<0&&next===minimum)||(amount>0&&next===maximum);
            });

            if(save){
                try{
                    window.localStorage.setItem(storageKey,String(next));
                }catch(error){
                    // Text sizing still works when browser storage is unavailable.
                }
            }
        };

        try{
            const saved=Number(window.localStorage.getItem(storageKey));
            if(Number.isInteger(saved)&&saved>=minimum&&saved<=maximum){
                scale=saved;
            }
        }catch(error){
            // Keep the default size if browser storage is unavailable.
        }
        setScale(scale,false);

        buttons.forEach(button=>{
            button.addEventListener('click',()=>{
                const amount=Number(button.dataset.postFontStep)||0;
                setScale(scale+amount);
            });
        });

        let resizeTimer=0;
        window.addEventListener('resize',()=>{
            window.clearTimeout(resizeTimer);
            resizeTimer=window.setTimeout(()=>{
                measureBaseFontSizes();
                setScale(scale,false);
            },120);
        },{passive:true});

        if(details&&window.matchMedia){
            const mobile=window.matchMedia('(max-width: 900px)');
            const syncTocState=event=>{
                if(event.matches){
                    details.removeAttribute('open');
                }else{
                    details.setAttribute('open','');
                }
            };

            syncTocState(mobile);
            if(mobile.addEventListener){
                mobile.addEventListener('change',syncTocState);
            }else if(mobile.addListener){
                mobile.addListener(syncTocState);
            }
        }

        const tocLinks=[...sidebar.querySelectorAll('.bp-post__toc-item a[href^="#"]')];
        const headings=[...content.querySelectorAll('h2[id],h3[id],h4[id]')];

        if(tocLinks.length&&headings.length&&'IntersectionObserver' in window){
            const linksById=new Map(tocLinks.map(link=>[
                link.getAttribute('href').slice(1),
                link
            ]));
            const visibleHeadings=new Map();
            const observer=new IntersectionObserver(entries=>{
                entries.forEach(entry=>{
                    if(entry.isIntersecting){
                        visibleHeadings.set(entry.target.id,entry.target);
                    }else{
                        visibleHeadings.delete(entry.target.id);
                    }
                });

                const current=[...visibleHeadings.values()]
                    .sort((a,b)=>a.getBoundingClientRect().top-b.getBoundingClientRect().top)[0];

                tocLinks.forEach(link=>link.removeAttribute('aria-current'));
                const active=current&&linksById.get(current.id);
                if(active)active.setAttribute('aria-current','location');
            },{
                rootMargin:'-18% 0px -68% 0px',
                threshold:0
            });

            headings.forEach(heading=>observer.observe(heading));
        }
    };

    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',init,{once:true});
    }else{
        init();
    }
})();
