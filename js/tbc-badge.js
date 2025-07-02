jQuery(function($){
    function updateFields(){
        var val = $('#tbc_badge_type').val();
        $('.tbc-badge-product, .tbc-badge-category, .tbc-badge-spend, .tbc-badge-xp, .tbc-badge-json').hide();
        if(val==='product') $('.tbc-badge-product').show();
        if(val==='category') $('.tbc-badge-category').show();
        if(val==='spend') $('.tbc-badge-spend').show();
        if(val==='xp') $('.tbc-badge-xp').show();
        if(val==='json') $('.tbc-badge-json').show();
    }
    $(document).on('change', '#tbc_badge_type', updateFields);
    updateFields();

    $('.tbc-upload-badge-icon').on('click',function(e){
        e.preventDefault();
        var $input = $('#tbc_badge_icon');
        var $preview = $('.tbc-badge-media-preview');
        var frame = wp.media({title:'Select Badge Icon',button:{text:'Use this icon'},multiple:false});
        frame.on('select',function(){
            var url = frame.state().get('selection').first().toJSON().url;
            $input.val(url);
            $preview.attr('src',url).show();
        });
        frame.open();
    });

    if(typeof $.fn.select2 !== 'undefined'){
        $('.wc-product-search').select2({
            width: '100%',
            multiple: true,
            closeOnSelect: false,
            ajax: {
                url: tbc_badge_admin.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function(params){
                    return {
                        term: params.term,
                        action: 'woocommerce_json_search_products_and_variations',
                        security: tbc_badge_admin.search_products_nonce
                    };
                },
                processResults: function(data){
                    var results = [];
                    $.each(data, function(id, text){ results.push({id:id, text:text}); });
                    return {results:results};
                }
            },
            placeholder: function(){ return $(this).data('placeholder') || 'Select a product'; },
            allowClear: true,
            dropdownParent: $('body'),
            dropdownCssClass: 'tbc-select2-dropdown'
        }).trigger('change');
        $('.wc-category-search').select2({
            width: '100%',
            multiple: true,
            closeOnSelect: false,
            ajax: {
                url: tbc_badge_admin.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function(params){
                    return {
                        term: params.term,
                        action: 'tbc_search_product_categories',
                        security: tbc_badge_admin.search_categories_nonce
                    };
                },
                processResults: function(data){
                    var results = [];
                    $.each(data, function(id, text){ results.push({id:id, text:text}); });
                    return {results:results};
                }
            },
            placeholder: function(){ return $(this).data('placeholder') || 'Select a category'; },
            dropdownParent: $('body'),
            dropdownCssClass: 'tbc-select2-dropdown'
        }).trigger('change');
    }
});
