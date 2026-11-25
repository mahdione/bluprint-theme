(()=>{
    'use strict';

    const toLatin=value=>value.replace(/[۰-۹٠-٩]/g,char=>{
        const map={'۰':'0','۱':'1','۲':'2','۳':'3','۴':'4','۵':'5','۶':'6','۷':'7','۸':'8','۹':'9','٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
        return map[char]||char;
    });

    document.querySelectorAll('[data-contact-form]').forEach(form=>{
        if(form.dataset.contactFormReady)return;
        form.dataset.contactFormReady='1';

        const phone=form.querySelector('input[type="tel"]');
        const message=form.querySelector('textarea');
        const counter=form.querySelector('[data-contact-counter]');

        const syncCounter=()=>{
            if(counter&&message)counter.textContent=String(message.value.length);
        };

        if(message){
            syncCounter();
            message.addEventListener('input',syncCounter);
        }

        const clearInvalid=()=>{
            form.querySelectorAll('.is-invalid').forEach(field=>field.classList.remove('is-invalid'));
        };

        if(phone)phone.addEventListener('input',clearInvalid);
        if(message)message.addEventListener('input',clearInvalid);

        form.addEventListener('submit',event=>{
            if(form.dataset.submitting==='1'){
                event.preventDefault();
                return;
            }

            if(phone)phone.value=toLatin(phone.value);
            form.dataset.submitting='1';
        });
    });
})();
