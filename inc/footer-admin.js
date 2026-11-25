jQuery(function($){
    let frame;
    $('#bluprint-footer-select').on('click',function(e){
        e.preventDefault();
        if(frame){frame.open();return;}
        frame=wp.media({title:'انتخاب تصویر فوتر',button:{text:'استفاده از تصویر'},multiple:false});
        frame.on('select',function(){
            const image=frame.state().get('selection').first().toJSON();
            $('#bluprint_footer_image').val(image.id);
            $('#bluprint-footer-preview').html('<img src="'+image.url+'" style="max-width:320px;height:auto">');
        });
        frame.open();
    });
    $('#bluprint-footer-remove').on('click',function(){
        $('#bluprint_footer_image').val('');
        $('#bluprint-footer-preview').empty();
    });
});