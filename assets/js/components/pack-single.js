(()=>{
    'use strict';

    const init=()=>{
        const form=document.querySelector('[data-pack-form]');
        if(!form)return;

        const config=window.BluprintPack||{};
        const rows=[...form.querySelectorAll('[data-pack-row]')];
        const totals=[...document.querySelectorAll('[data-pack-total]')];
        const selectedOutputs=[...document.querySelectorAll('[data-pack-selected]')];
        const submitButtons=[...document.querySelectorAll('[data-pack-submit]')];
        const messages=[...document.querySelectorAll('[data-pack-message]')];
        const decimals=Number(config.decimals)||0;

        const setMessage=(text,success=false)=>{
            messages.forEach(element=>{
                element.textContent=text;
                element.classList.toggle('is-success',success);
            });
        };
        const numberFormat=new Intl.NumberFormat('en-US',{
            minimumFractionDigits:decimals,
            maximumFractionDigits:decimals
        });

        const formatMoney=value=>{
            const amount=numberFormat.format(Math.max(0,Number(value)||0));
            return String(config.priceFormat||'%2$s %1$s')
                .replace('%1$s',config.currency||'')
                .replace('%2$s',amount);
        };

        const clamp=value=>Math.max(0,Math.min(99,Number.isFinite(value)?Math.trunc(value):0));

        const getInput=row=>row.querySelector('input[type="number"]');

        const update=()=>{
            let total=0;
            let units=0;

            rows.forEach(row=>{
                const input=getInput(row);
                if(!input)return;

                const unavailable=row.classList.contains('is-unavailable');
                const quantity=unavailable?0:clamp(Number.parseInt(input.value,10)||0);
                const unitPrice=Number.parseFloat(row.dataset.unitPrice)||0;
                const lineTotal=unitPrice*quantity;

                total+=lineTotal;
                units+=quantity;

                row.classList.toggle('is-removed',quantity===0);

                const lineOutput=row.querySelector('[data-line-total]');
                if(lineOutput)lineOutput.textContent=formatMoney(lineTotal);

                const removeButton=row.querySelector('[data-pack-remove]');
                if(removeButton){
                    removeButton.textContent=quantity===0?'افزودن':'حذف';
                    removeButton.setAttribute(
                        'aria-label',
                        (quantity===0?'افزودن ':'حذف ')+row.querySelector('.bp-pack-item__title')?.textContent?.trim()
                    );
                }

                const minus=row.querySelector('[data-qty-action="minus"]');
                const plus=row.querySelector('[data-qty-action="plus"]');
                if(minus)minus.disabled=unavailable||quantity<=0;
                if(plus)plus.disabled=unavailable||quantity>=99;
            });

            totals.forEach(output=>{
                output.textContent=formatMoney(total);
            });

            selectedOutputs.forEach(output=>{
                output.textContent=numberFormat.format(units);
            });

            submitButtons.forEach(button=>{
                button.disabled=units<1;
            });

            setMessage('');

            return units;
        };

        const setQuantity=(row,value)=>{
            const input=getInput(row);
            if(!input||input.disabled)return;

            const next=clamp(value);
            input.value=String(next);
            update();
        };

        rows.forEach(row=>{
            const input=getInput(row);
            if(!input)return;

            input.addEventListener('input',()=>{
                const value=Number.parseInt(input.value,10);
                if(Number.isFinite(value)&&value>99)input.value='99';
                update();
            });

            input.addEventListener('change',()=>{
                const value=clamp(Number.parseInt(input.value,10)||0);
                input.value=String(value);
                update();
            });

            row.querySelectorAll('[data-qty-action]').forEach(button=>{
                button.addEventListener('click',()=>{
                    const current=clamp(Number.parseInt(input.value,10)||0);
                    const step=button.dataset.qtyAction==='plus'?1:-1;
                    setQuantity(row,current+step);
                });
            });

            const removeButton=row.querySelector('[data-pack-remove]');
            removeButton?.addEventListener('click',()=>{
                const current=clamp(Number.parseInt(input.value,10)||0);

                if(current>0){
                    row.dataset.lastQty=String(current);
                    setQuantity(row,0);
                }else{
                    setQuantity(row,clamp(Number.parseInt(row.dataset.lastQty,10)||1));
                }
            });
        });

        // Validate before the shared cart API handler starts the request.
        document.addEventListener('submit',event=>{
            if(event.target!==form)return;

            if(update()<1){
                event.preventDefault();
                event.stopImmediatePropagation();
                setMessage('حداقل یک محصول را برای افزودن به سبد انتخاب کن.');
                return;
            }

            setMessage('در حال افزودن پک به سبد خرید…');
        },true);

        document.addEventListener('bp:pack-added',()=>{
            setMessage('پک به سبد خرید شما اضافه شد',true);
        });

        document.addEventListener('bp:pack-add-failed',()=>{
            setMessage('افزودن پک انجام نشد؛ دوباره تلاش کن.');
        });

        update();
    };

    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',init,{once:true});
    }else{
        init();
    }
})();
