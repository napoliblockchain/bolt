<?php
/**
 * @author Sergio Casizzone
 * Esegue in background programmi e comandi per windows e linux
 *
 * utilizza la nuova libreria phpseclib
 * [Browse Git](https://github.com/phpseclib/phpseclib)
 */
class Seclib
{
    public function execInBackground($cmd)
    {
        if (substr(php_uname(), 0, 7) == "Windows"){
            pclose(popen("start /B ". $cmd, "r"));
        } else {
            $phpseclib = Yii::app()->basePath . '/extensions/phpseclib/vendor/autoload.php';
            if (true === file_exists($phpseclib) && true === is_readable($phpseclib)){
                require_once $phpseclib;
            } else {
                throw new Exception('phpseclib Library could not be loaded');
            }
            $ssh = new \phpseclib\Net\SSH2('localhost', 22);

            if (!$ssh->login(crypt::Decrypt(Settings::load()->sshuser), crypt::Decrypt(Settings::load()->sshpassword))) {
                return array('error' => 'Login to localhost server failed');
            }
            $action = $cmd . " > /dev/null &";
            $ssh->exec($action);
        }
    }
}
