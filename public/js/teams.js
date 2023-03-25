$(document).ready(function(){
    $('.btnAddTeam').click(function(){
        $('#mdlTeams').modal('show');
        $('#team_id').val(-1);
        $('.modal-title').text('New Team')
        $(".invalid-feedback").children("strong").text("");
    })
    $('.btnmdlclose').click(function(){
        $('#mdlDepartment').modal('hide');
    })

    $('#frm_teams').submit(function(e){
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        $(".invalid-feedback").children("strong").text("");

        $.ajax({
            type: 'POST',
            url:base_url +'/teams',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".creatBtn").html('please wait..');
                $(".creatBtn").prop('disabled', true);
            },
            success: function(response) {
                console.log(response);
                if (response.status == true) {
                    $('#frm_teams')[0].reset();
                    alertify.success(response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);

                } else {
                    alertify.error(response.message);
                    $(".creatBtn").prop('disabled', false);
                    $(".creatBtn").html('Submit');
                }

            },
            error: function(response) {
                $(".creatBtn").prop('disabled', false);
                $(".creatBtn").html('Submit');
                if (response.responseJSON.status === 400) {
                    let errors = response.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        $("#" + key + "Input").addClass("is-invalid");
                        $("#" + key + "-input-error").children("strong").text(errors[key][0]);
                    });
                }
            }
        });
    });

    $('.btn_edit_team').click(function(e){
        let dept_id = $(this).attr('teamid')
        editUrl = base_url + '/teams/' + dept_id + '/edit';
        $.get( editUrl, function (response) {
            if(response.status == true){
                 let team = response.data;
                $('.modal-title').text('Edit Team')
                $('.creatBtn').text('Update');

                $('#team_id').val(team.id);
                $('#name').val(team.name);
                $('#description').val(team.description);
                $('#branch').val(team.department);
                $('#mdlTeams').modal('show');

            }else{
                alertify.error(response.message);
            }
        });
    });
})


// Manage Team Members
$(document).ready(function(){

        $('.btnAddMembers').click(function(){
            let teamid = $(this).attr('team_id')
            let departmentid = $(this).attr('departmentid')
            $('#teamid').val(teamid);


            $('#userslist-unassigned li').remove();
            $('#userslist li').remove();

            $.ajax({
                type: 'GET',
                url: base_url +"/teams/teammembers",
                data: {'department':departmentid, 'teamid': teamid },
                success: function(response) {
                    if(response.status){
                        let signedUser      = response.signedUsers;
                        let unSignedUser    = response.unsignedUsers;

                        signedUser.forEach(item => {
                                let lst = '<li usr-id="'+ item.users.id +'" mem-id="'+ item.id+'" class="list-group-item">'+
                                                '<i class="fas fa-arrows-alt handle mr-2"></i>'+ item.users.name +'(' + item.users.roles[0].name  +')'
                                                '</a>'+
                                            '</li>';

                                $('#userslist').append(lst);
                        });

                        unSignedUser.forEach(item => {
                                let lst1 = '<li usr-id="'+ item.id +'" mem-id=""  class="list-group-item">'+
                                                '<i class="fas fa-arrows-alt handle mr-2"></i>'+ item.name +'(' + item.roles[0].name +')'
                                                '</a>'+
                                            '</li>';

                                $('#userslist-unassigned').append(lst1);
                        });

                        $('#teams_id').val(teamid);
                        $('#mdlDeptUsers').modal('show');
                        $('.modal-title').text('Manage Member')

                    }
                },
                error:function(response){
                    console.log(response);
                }
            });

        })
        $('.btnmdlclose').click(function(){
            $('#mdlDeptUsers').modal('hide');
        })


        $('#userslist').sortable({
            group: 'list',
            handle: '.list-group-item',
            cancel: '',
            animation: 200,
            ghostClass: 'ghost',
            onRemove: function (evt) {
               let usr = evt.item
               let userid =  $(usr).attr('mem-id');
               removeUserFromTeam(userid);
            },
            onAdd: function (evt) {
               let usr = evt.item
               let userid =  $(usr).attr('usr-id');
               addUserToTeam(userid, evt);
            },
        });

        // List 2
        $('#userslist-unassigned').sortable({
            group: 'list',
            animation: 200,
            ghostClass: 'ghost',
            sort: false,
        });

});


function addUserToTeam(user, event){
    let department = $('#departmentid').val();
    let teamid = $('#teamid').val();
    $.ajax({
        type: 'POST',
        url: base_url + '/teams/members/add',
        data: { 'userid' : user , 'teamid': teamid , 'deptid': department },
        dataType:'json',
        success: function(response) {
            if (response.status == true) {
                let ssss = event.item;
                $(ssss).attr('mem-id', response.data.id)
            }
        },
    });
}
function removeUserFromTeam(user){
        $.ajax({
            type: 'POST',
            url: base_url + '/teams/members/remove',
            data: { 'memberid' : user },
            dataType:'json',
            success: function(response) {
                console.log(response);
            },
            error: function(response) {
                console.log(response);

            }
        });
}
