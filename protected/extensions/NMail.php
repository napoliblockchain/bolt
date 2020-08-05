<?php
/**
 * @author Sergio Casizzone
 * @class Classe che raccolgie funzioni di invio email
 * @param controller -> la vista da mostrare
 * @param encryptedUserId -> lo userid criptato
 * @param email -> la mail dell'user (non è obbligatorio)
 * @param password -> la password della prima iscrizione (non è obbligatoria)
 * @param activation_code -> il codice attivazione dopo la prima iscrizione (non è obbligatoria)
 *
 */
class NMail {

    public function SendMail($controller, $encryptedUserId, $email = '', $password = '', $activation_code = ''){
        //Se mi trovo in ufficio è inutile tentare di inviare una mail....
        if (gethostname()=='CGF6135T'){
          return true;
        }

		$message = new YiiMailMessage;
		$message->view = $controller; //this points to the file xxxx.php inside the view path
        $model=Users::model()->findByPk(crypt::Decrypt($encryptedUserId));
        if (null !== $model)
            $params = (array) $model->attributes;

        $params['encryptedUserId']=$encryptedUserId;
        $params['password']=$password;
        $params['activation_code']=crypt::Encrypt($activation_code.','.$encryptedUserId);

        $filename = dirname(Yii::app()->getBasePath()) . '/css/images/logomail.png';
		$image = Swift_Image::fromPath($filename);
		$logo = $message->embed($image);
		$params['logo']  = $logo;

        if (null !== $model){
            $settingsUser = Settings::loadUser($model->id_user);
            foreach ($settingsUser as $key => $value){
                $params[$key] = $value;
            }
        }

        $fromEmail = Yii::app()->params['adminEmail'];

        switch ($controller){
            case 'recovery':
                $subject = Yii::t('notify','{application} - Password Reset',array('{application}'=>Yii::app()->params['shortName']));
                break;
            case 'users':
                $subject = Yii::t('notify','{application} - User signup',array('{application}'=>Yii::app()->params['shortName']));
                break;

            case 'signup':
                $subject = Yii::t('notify','{application} - User signup',array('{application}'=>Yii::app()->params['shortName']));
                break;

            case 'contact':
                // adatto il parametro password che contiene a questo punto un array
                $subject = $params['password']['subject'];
                $email = Yii::app()->params['adminEmail'];
        		$fromEmail = $params['password']['email'];
                break;

            default:
                $subject = 'Wellcome in '.Yii::app()->params['shortName'];
        }
        // echo '<pre>'.print_r($params,true).'</pre>';
        // exit;

        $message->subject = $subject;
		$message->setBody($params, 'text/html');
        $message->addTo($email);
		$message->from = $fromEmail;

		Yii::app()->mail->send($message);

        return true;
	}
}
?>
