$( document ).ready(function() {
    $('#searchInput').keyup(function() {
        showMatchingWebs($(this).val());
    });

    function showMatchingWebs(str){
        $('.accordion').each(function(){
            if($(this).data('name').toLowerCase().indexOf(str.toLowerCase()) >= 0 || $(this).data('domain').toLowerCase().indexOf(str.toLowerCase()) >= 0){
                $(this).show();
            }else{
                $(this).hide();
            }
        });
    }
});