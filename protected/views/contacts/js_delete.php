<?php
$getProviderUser = Yii::app()->createUrl('contacts/getuser'); // carica i dati social del contatto
$deleteContact = Yii::app()->createUrl('contacts/delete');


$myDeleteScript = <<<JS

var selectedUser = 0;

var selectContact = document.querySelectorAll('.deleteContact');

for (var i = 0; i < selectContact.length; i++) {
    selectContact[i].addEventListener('click', function(event) {
			var id_social = this.id.substring(11);

			$.ajax({
				url:'{$getProviderUser}',
				type: "POST",
				data: {'id_social': id_social},
				dataType: "json",
				success:function(data){
					//console.log(data);
					$("#avatarImage").prop("src",data.picture);
					$("#avatarProvider").prop("src",'css/images/'+data.oauth_provider+'.svg');
					$("#avatarName").html(data.first_name +' '+ data.last_name);
					$("#avatarUsername").html(data.username);
                    $("#avatarAddress").html(data.address);
                    $("#linkToAvatarAddress").prop("href",data.linkToAvatarAddress);
                    $("#linkToCheckBalances").prop("href",data.linkToCheckBalances);
					$("#deleteUserModal").modal("show");
					//href
					//$("#deleteUserButton").prop("href",'{$deleteContact}'+'&tg_id='+tg_id);
					selectedUser = id_social;
				},
				error: function(j){
					console.log(j);
				}
			});

    });
}


$("a[id='deleteUserButton']").click(function(){
	$.ajax({
		url: '{$deleteContact}',
		type: "POST",
		data: {'id_social': selectedUser},
		success:function(data){
			var row = '#contact_02_'+selectedUser;
			$('#deleteUserModal').modal('hide');
			$(row).parent('td').parent('tr').remove();
		},
		error: function(j){
			//console.log(j);
		}
	});


});



JS;
Yii::app()->clientScript->registerScript('myDeleteScript', $myDeleteScript, CClientScript::POS_END);
?>
<!-- 51sangiu -->
