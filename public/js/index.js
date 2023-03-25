let base_url = $('meta[name="app_url"]').attr('content')

let local = localStorage.getItem("vertical-menu");
if(local == 'true' ){
    $("body").removeClass("vertical-collpsed");
    local =!local
}else{
    $("body").toggleClass("vertical-collpsed");
    local =!local
}

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

    $("#vertical-menu-btn").on("click", function() {
        let local = localStorage.getItem("vertical-menu");
        if(local == 'true' ){
            localStorage.setItem("vertical-menu", "false");
            $("body").toggleClass("sidebar-enable")
            $("body").toggleClass("vertical-collpsed");
        }else{
            localStorage.setItem("vertical-menu", "true");
            $("body").toggleClass("sidebar-enable")
            $("body").removeClass("vertical-collpsed");

        }
    })

});
