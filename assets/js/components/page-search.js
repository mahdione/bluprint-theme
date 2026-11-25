(()=>{
'use strict';

const initForm=form=>{
    if(form.dataset.pageSearchReady)return;
    form.dataset.pageSearchReady='1';

    const input=form.querySelector('.bp-page-search__input');
    const clear=form.querySelector('[data-page-search-clear]');
    if(!input)return;

    const sync=()=>{
        const hasValue=input.value.trim()!=='';
        form.classList.toggle('has-value',hasValue);
        if(clear)clear.hidden=!hasValue;
    };

    sync();

    input.addEventListener('input',()=>{
        form.classList.remove('is-invalid');
        sync();
    });

    clear?.addEventListener('click',()=>{
        input.value='';
        form.classList.remove('is-invalid');
        sync();
        input.focus();
    });

    form.addEventListener('submit',event=>{
        if(input.value.trim()===''){
            event.preventDefault();
            form.classList.add('is-invalid');
            input.focus();
        }
    });
};

const initShortcut=()=>{
    document.addEventListener('keydown',event=>{
        if(event.key!=='/'||event.metaKey||event.ctrlKey||event.altKey)return;

        const active=document.activeElement;
        if(active&&(active.tagName==='INPUT'||active.tagName==='TEXTAREA'||active.tagName==='SELECT'||active.isContentEditable))return;

        const input=document.querySelector('.bp-page-search__input');
        if(!input)return;

        event.preventDefault();
        input.focus();
        input.select();
    });
};

const start=()=>{
    document.querySelectorAll('[data-page-search]').forEach(initForm);
    initShortcut();
};

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',start,{once:true});
}else{
    start();
}
})();
