(()=>{
'use strict';
jQuery($=>{
    const root=$('#bp-term-order-root');
    if(!root.length||typeof BP_Term_Order==='undefined')return;
    const status=$('.bp-term-order__status');
    let pending=0;

    root.find('.bp-term-order__list').each(function(){
        const list=$(this);
        list.sortable({
            axis:'y',
            handle:'.bp-term-order__handle',
            items:'> li',
            placeholder:'bp-term-order__placeholder',
            tolerance:'pointer',
            update(){
                const order=list.children('li').map(function(){
                    return $(this).attr('data-term-id');
                }).get();

                status.text(BP_Term_Order.saving);
                root.find('.bp-term-order__list').sortable('disable');
                pending++;

                $.ajax({
                    url:BP_Term_Order.ajaxUrl,
                    method:'POST',
                    data:{
                        action:'bluprint_save_term_order',
                        nonce:BP_Term_Order.nonce,
                        taxonomy:root.data('taxonomy'),
                        parent:list.data('parent-id'),
                        order
                    }
                }).done(response=>{
                    status.text(response?.success?BP_Term_Order.saved:BP_Term_Order.error);
                }).fail(()=>{
                    status.text(BP_Term_Order.error);
                }).always(()=>{
                    pending--;
                    if(!pending)root.find('.bp-term-order__list').sortable('enable');
                });
            }
        });
    });
});
})();