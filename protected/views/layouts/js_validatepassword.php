<?php
Yii::app()->language = ( isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'it' );
Yii::app()->sourceLanguage = ( isset($_COOKIE['langSource']) ? $_COOKIE['langSource'] : 'it_it' );
new JsTrans('js',Yii::app()->language); // javascript translation

$validatepwd = <<<JS

function validatePassword(password,id) {
	$('#'+id).show();
	document.getElementById(id).style.backgroundColor = 'transparent';
	document.getElementById(id).style.color = 'white';

	// Do not show anything when the length of password is zero.
	if (password.length === 0) {
	$('#crypt_password_em_').text('');
		return;
	}
	// Create an array and push all possible values that you want in password
	var matchedCase = new Array();
	matchedCase.push("[$@$!%*#?&/\().:;-_]"); // Special Charector
	matchedCase.push("[A-Z]");      // Uppercase Alpabates
	matchedCase.push("[0-9]");      // Numbers
	matchedCase.push("[a-z]");     // Lowercase Alphabates

	// Check the conditions
	var ctr = 0;
	for (var i = 0; i < matchedCase.length; i++) {
	  if (new RegExp(matchedCase[i]).test(password)) {
		  ctr++;
	  }
	}
	// Display it
	var background = "";
	var strength = "";
	if (password.length < 8)
		strength = Yii.t('js',"Too short and ");
	switch (ctr) {
	  case 0:
	  case 1:
	  case 2:
		  strength += Yii.t('js','Weak');
		  background = "red";
		  break;
	  case 3:
		  strength += Yii.t('js',"Medium");
		  background = "orange";
		  break;
	  case 4:
		  strength += Yii.t('js',"Strong");
		  background = "green";
		  break;
	}
	$('#'+id).text(strength);
	document.getElementById(id).style.backgroundColor = background;
}



JS;
Yii::app()->clientScript->registerScript('validatepwd', $validatepwd, CClientScript::POS_END );
?>
