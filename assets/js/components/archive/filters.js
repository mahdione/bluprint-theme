(()=>{
'use strict';

const panel=document.getElementById('bp-archive-filter-panel');
const open=document.querySelector('.bp-filter-open');
const close=document.querySelector('.bp-filter-close');
const form=panel?.querySelector('form');

if(panel&&open){
    const backdrop=document.createElement('div');
    backdrop.className='bp-archive-backdrop';
    backdrop.setAttribute('aria-hidden','true');
    document.body.appendChild(backdrop);

    const set=v=>{
        panel.classList.toggle('is-open',v);
        backdrop.classList.toggle('is-active',v);
        document.body.classList.toggle('bp-filter-lock',v);
        open.setAttribute('aria-expanded',String(v));
        if(v)panel.querySelector('input,button')?.focus();
        else open.focus();
    };

    open.addEventListener('click',()=>set(true));
    close?.addEventListener('click',()=>set(false));
    backdrop.addEventListener('click',()=>set(false));

    document.addEventListener('keydown',e=>{
        if(e.key==='Escape'&&panel.classList.contains('is-open'))set(false);
    });
}

const slider=form?.querySelector('[data-price-slider]');

if(slider){
    const min=slider.querySelector('[data-price-min]');
    const max=slider.querySelector('[data-price-max]');
    const minOutput=form.querySelector('[data-price-min-value]');
    const maxOutput=form.querySelector('[data-price-max-value]');
    const step=Number(min.step)||1;
    const minDefault=Number(min.min);
    const maxDefault=Number(max.max);
    const number=new Intl.NumberFormat('en-US',{maximumFractionDigits:0});

    const update=source=>{
        let a=Number(min.value);
        let b=Number(max.value);

        if(a>b-step){
            if(source===min)a=Math.max(minDefault,b-step);
            else b=Math.min(maxDefault,a+step);
        }

        min.value=String(a);
        max.value=String(b);

        const range=maxDefault-minDefault||1;
        const start=((a-minDefault)/range)*100;
        const end=((b-minDefault)/range)*100;

        slider.style.setProperty('--range-start',start+'%');
        slider.style.setProperty('--range-end',end+'%');

        minOutput.textContent=number.format(a);
        maxOutput.textContent=number.format(b);
    };

    min.addEventListener('input',()=>update(min));
    max.addEventListener('input',()=>update(max));

    min.addEventListener('pointerdown',()=>min.style.zIndex='4');
    max.addEventListener('pointerdown',()=>max.style.zIndex='4');
    min.addEventListener('pointerup',()=>min.style.zIndex='2');
    max.addEventListener('pointerup',()=>max.style.zIndex='3');

    update();

    form.addEventListener('submit',()=>{
        if(Number(min.value)<=minDefault)min.removeAttribute('name');
        if(Number(max.value)>=maxDefault)max.removeAttribute('name');
    });
}
})();