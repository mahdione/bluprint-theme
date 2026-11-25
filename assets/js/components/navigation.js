document.addEventListener('DOMContentLoaded',()=>{
    const header=document.querySelector('.site-header');
    const toggle=document.querySelector('.mobile-menu-toggle');
    const navigation=document.querySelector('.site-navigation');
    const backdrop=document.querySelector('.site-navigation-backdrop');
    const cart=document.querySelector('.header-cart');

    if(header){
        let ticking=false;

        const updateHeader=()=>{
            const y=window.scrollY||window.pageYOffset||0;
            header.classList.toggle('is-scrolled',y>12);

            const max=Math.max(0,document.documentElement.scrollHeight-window.innerHeight);
            const progress=max>0?Math.min(1,Math.max(0,y/max)):0;
            header.style.setProperty('--scroll-progress',progress.toFixed(4));

            ticking=false;
        };

        const requestHeaderUpdate=()=>{
            if(ticking)return;
            ticking=true;
            window.requestAnimationFrame(updateHeader);
        };

        window.addEventListener('scroll',requestHeaderUpdate,{passive:true});
        window.addEventListener('resize',requestHeaderUpdate,{passive:true});
        updateHeader();
    }

    if(cart){
        let lastCount=cart.querySelector('.header-cart__count')?.textContent.trim()||'';
        let bumpTimer=null;

        const observer=new MutationObserver(()=>{
            const count=cart.querySelector('.header-cart__count');
            if(!count)return;

            const next=count.textContent.trim();
            if(next===lastCount)return;
            lastCount=next;

            count.classList.remove('is-bumped');
            void count.offsetWidth;
            count.classList.add('is-bumped');

            clearTimeout(bumpTimer);
            bumpTimer=setTimeout(()=>count.classList.remove('is-bumped'),620);
        });

        observer.observe(cart,{childList:true,subtree:true,characterData:true});
    }

    if(!toggle||!navigation)return;

    const desktop=window.matchMedia('(min-width:1025px)');

    const focusableElements=()=>Array.from(
        navigation.querySelectorAll(
            'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'
        )
    ).filter(element=>element.getClientRects().length>0);

    const setAriaHidden=hidden=>{
        if(desktop.matches){
            navigation.removeAttribute('aria-hidden');
            return;
        }
        navigation.setAttribute('aria-hidden',hidden?'true':'false');
    };

    const close=({restoreFocus=false}={})=>{
        toggle.setAttribute('aria-expanded','false');
        toggle.setAttribute('aria-label','باز کردن منو');
        navigation.classList.remove('is-open');
        backdrop?.classList.remove('is-visible');
        document.body.classList.remove('menu-open');
        setAriaHidden(true);
        if(restoreFocus)toggle.focus();
    };

    const open=()=>{
        if(desktop.matches)return;
        toggle.setAttribute('aria-expanded','true');
        toggle.setAttribute('aria-label','بستن منو');
        navigation.classList.add('is-open');
        backdrop?.classList.add('is-visible');
        document.body.classList.add('menu-open');
        setAriaHidden(false);

        /* Move focus into the drawer, but never into a field: focusing the search
           input here opens the mobile keyboard as soon as the menu is opened. */
        const items=focusableElements();
        const initial=items.find(element=>!element.matches('input,textarea,select'));
        if(initial)window.requestAnimationFrame(()=>initial.focus({preventScroll:true}));
    };

    toggle.addEventListener('click',()=>{
        toggle.getAttribute('aria-expanded')==='true'?close({restoreFocus:true}):open();
    });

    backdrop?.addEventListener('click',()=>close({restoreFocus:true}));

    /* Keep the two drawers from fighting: opening the basket closes the menu. */
    cart?.addEventListener('click',()=>{
        if(toggle.getAttribute('aria-expanded')==='true')close();
    });

    document.addEventListener('keydown',event=>{
        if(toggle.getAttribute('aria-expanded')!=='true')return;

        if(event.key==='Escape'){
            close({restoreFocus:true});
            return;
        }

        if(event.key!=='Tab')return;

        const items=focusableElements();
        if(!items.length)return;

        const first=items[0];
        const last=items[items.length-1];

        if(event.shiftKey&&document.activeElement===first){
            event.preventDefault();
            last.focus();
        }else if(!event.shiftKey&&document.activeElement===last){
            event.preventDefault();
            first.focus();
        }
    });

    navigation.addEventListener('click',event=>{
        if(event.target.closest('a'))close();
    });

    const handleBreakpointChange=()=>{
        if(desktop.matches){
            close();
            navigation.removeAttribute('aria-hidden');
        }else if(toggle.getAttribute('aria-expanded')!=='true'){
            setAriaHidden(true);
        }
    };

    if(desktop.addEventListener){
        desktop.addEventListener('change',handleBreakpointChange);
    }else{
        desktop.addListener(handleBreakpointChange);
    }

    handleBreakpointChange();
});
