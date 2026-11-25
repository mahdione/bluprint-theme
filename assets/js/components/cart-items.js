document.addEventListener('DOMContentLoaded',()=>{
    'use strict';

    const drawer=document.querySelector('.bp-cart-drawer');
    const checkoutUrl=window.BPCart?.checkoutUrl;

    /* Add a direct checkout shortcut beside the drawer close control. */
    const ensureCheckout=()=>{
        if(!drawer||!checkoutUrl)return;

        const header=drawer.querySelector('.bp-cart-drawer__header');
        if(!header)return;

        let actions=header.querySelector('.bp-cart-drawer__actions');
        if(!actions){
            actions=document.createElement('div');
            actions.className='bp-cart-drawer__actions';

            const close=header.querySelector('.bp-cart-drawer__close');
            if(close){
                close.parentNode.insertBefore(actions,close);
                actions.appendChild(close);
            }else{
                header.appendChild(actions);
            }
        }

        if(!actions.querySelector('.bp-cart-drawer__checkout')){
            const link=document.createElement('a');
            link.className='bp-cart-drawer__checkout';
            link.href=checkoutUrl;
            link.setAttribute('aria-label','ادامه و پرداخت');
            link.innerHTML='<span>پرداخت</span><span aria-hidden="true">←</span>';

            const close=actions.querySelector('.bp-cart-drawer__close');
            actions.insertBefore(link,close);
        }
    };

    ensureCheckout();

    /* The commerce API re-renders the drawer header after AJAX cart updates. */
    new MutationObserver(ensureCheckout).observe(document.body,{childList:true,subtree:true});
});
