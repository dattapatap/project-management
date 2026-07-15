let base_url = $('meta[name="app_url"]').attr('content')

$("body").removeClass("vertical-collpsed");

$(document).ready(function(){

    $('.mark-as-read').click(function(e) {
        let id = $(this).data('id');
        var div = $(this);
        $.ajax({
                type: 'POST',
                url: base_url+ '/mark-as-read',
                data: {id},
                success: function(response) {
                    console.log('Succes!',response);
                },
            });
    });

});
