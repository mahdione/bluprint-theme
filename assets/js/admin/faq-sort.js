jQuery(function($){
    const $list=$('#the-list');
    if(!$list.length||!window.bluprintFaqSort)return;

    $list.sortable({
        items:'tr',
        axis:'y',
        cursor:'move',
        handle:'.column-title',
        tolerance:'pointer',
        update:function(){
            const order=$list.find('tr').map(function(){
                return Number(this.id.replace('post-',''));
            }).get().filter(Boolean);

            $.post(bluprintFaqSort.ajaxUrl,{
                action:'bluprint_faq_sort',
                nonce:bluprintFaqSort.nonce,
                order
            }).done(function(response){
                if(!response.success)window.alert('ذخیره ترتیب انجام نشد.');
            }).fail(function(){
                window.alert('خطا در ارتباط با سرور.');
            });
        }
    });
});