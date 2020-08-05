<?php
$getProviderUser = Yii::app()->createUrl('contacts/getuser'); // carica i dati social del contatto
$saveContact = Yii::app()->createUrl('contacts/save'); // salva il contatto in rubrica

$myAddScript = <<<JS
var selectedUser = new Array();
function addContact(id_social){
	console.log('[selected Social User id]',id_social);

	if (!selectedUser[id_social]){
		$.ajax({
			url:'{$getProviderUser}',
			type: "POST",
			data: {'id_social': id_social},
			dataType: "json",
			success:function(data){
				console.log(data);
				$("#avatarImage").prop("src",data.picture);
				$("#avatarProvider").prop("src",'css/images/'+data.oauth_provider+'.svg');
				$("#avatarName").html(data.first_name +' '+ data.last_name);
				$("#avatarUsername").html(data.username);
				// $("#avatarEmail").html(data.email);
				$("#avatarAddress").html(data.address);
				$("#avatarUserId").val(data.id_user);
				$("#avatarTGUserId").val(id_social);
				$("#addUserModal").modal("show");
			},
			error: function(j){
				console.log(j);
			}
		});
	}
}

$("button[name='addUserButton']").click(function(){
	id_social = $("#avatarTGUserId").val();
	html = '<button class="btn alert-secondary text-light img-cir" style="padding:2.5px; width:30px; height:30px;"><i class="fa fa-check"></i></button>';

	$.ajax({
		url: '{$saveContact}',
		type: "POST",
		data:{
			'id_social'	: id_social,
		},
		dataType: "json",
		success:function(data){
			$('#addUserModal').modal('hide');
			$("#add_contact_"+id_social).html(html);
			selectedUser[id_social] = true;
		},
		error: function(j){
			console.log(j);
		}
	});

});


JS;
Yii::app()->clientScript->registerScript('myAddScript', $myAddScript, CClientScript::POS_END);
?>
<!-- 51sangiu -->
