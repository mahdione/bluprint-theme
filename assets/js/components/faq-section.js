document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('.bp-faq-section').forEach(section=>{
        section.addEventListener('click',event=>{
            const button=event.target.closest('.bp-faq-item__trigger');
            if(!button||!section.contains(button))return;

            const selectedItem=button.closest('.bp-faq-item');
            const shouldOpen=button.getAttribute('aria-expanded')!=='true';

            section.querySelectorAll('.bp-faq-item').forEach(item=>{
                const trigger=item.querySelector('.bp-faq-item__trigger');
                const panel=item.querySelector('.bp-faq-item__panel');
                const icon=trigger.querySelector('.bp-faq-item__icon');
                const isSelected=item===selectedItem&&shouldOpen;

                trigger.setAttribute('aria-expanded',String(isSelected));
                panel.setAttribute('aria-hidden',String(!isSelected));
                panel.toggleAttribute('inert',!isSelected);
                item.classList.toggle('is-open',isSelected);
                icon.textContent=isSelected?'−':'+';
            });
        });
    });
});