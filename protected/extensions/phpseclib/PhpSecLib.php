<?php
/**
 * PhpSecLib.php
 *
 * @author    Daniel Szuk <argent_hun@hotmail.com>
 * @author    phpseclib <http://phpseclib.sourceforge.net>
 * @version   0.2.1
 * @category  ext
 */

/**
 * Yii PhpSecLib is a Yii wrapper around phpseclib 0.3.6
 *
 * Add to your components in config/main.php:
 * 'phpseclib' => array(
 *     'class' => 'ext.phpseclib.PhpSecLib'
 * ),
 *
 * Changelog:
 * 0.2: 
 * - require_once() used relative path in the autoload
 * 
 * 0.2.1:
 * - new phpseclib version: 0.3.6
 * - createSystemSSHAgent() method added - more info: https://github.com/phpseclib/phpseclib/blob/master/phpseclib/System/SSH_Agent.php
 * 
 * Examples:
 * 
 * // http://phpseclib.sourceforge.net/math/examples.html
 * $a = Yii::app()->phpseclib->createBigInteger(5);
 * $b = Yii::app()->phpseclib->createBigInteger(30);
 * echo $a->add($b);
 * 
 * // http://phpseclib.sourceforge.net/math/examples.html#isprime
 * var_dump(Yii::app()->phpseclib->createBigInteger(15485863)->isPrime());
 *
 * // http://phpseclib.sourceforge.net/rsa/examples.html
 * $keys = Yii::app()->phpseclib->createRSA()->createKey();
 * print_r($keys);
 * 
 * // http://phpseclib.sourceforge.net/ssh/auth.html
 * $ssh = Yii::app()->phpseclib->createSSH2('www.domain.tld');
 * if (!$ssh->login('username', 'password')) {
 *     exit('Login Failed');
 * }
 * echo $ssh->exec('pwd');
 * 
 * // http://phpseclib.sourceforge.net/x509/examples.html#getpublickey
 * $x509 = Yii::app()->phpseclib->createX509();
 * $cert = $x509->loadX509("...");
 * echo $x509->getPublicKey();
 * 
 * Symmetric key encryption:
 * // http://phpseclib.sourceforge.net/crypt/examples.html
 * 
 * $cipher = Yii::app()->phpseclib->createAES();
 * $cipher->setKey('abcdefghijklmnopijklmnop');
 * $encrypted = $cipher->encrypt("helloworld");
 * 
 * $cipher = Yii::app()->phpseclib->createAES();
 * $cipher->setKey('abcdefghijklmnopijklmnop');
 * echo $cipher->decrypt($encrypted);
 * 
 * // hash:
 * $text = "this is a test";
 * $hash = Yii::app()->phpseclib->hash("sha1",$text); // available hashes (first parameter): md2, md5, md5-96, sha1, sha1-96, sha256, sha384, and sha512
 * var_dump(bin2hex($hash) === sha1($text)); // bool(true)
 * 
 * // random:
 * var_dump(bin2hex(Yii::app()->phpseclib->random(5))); // string(10) "d7244e5da1"
 * 
 * phpseclib without Yii::app()->phpseclib component:
 * 
 * please add the "phpseclib" to the preload section in the config:
 * 'preload'=>array('log','phpseclib'), 
 * and after that you can use the normal instantiation, e.g.:
 * $cipher = new Crypt_AES();
 * 
 * if you want to use the phpseclib constants, please use the preload method, e.g.:
 *  
 * Yii::app()->phpseclib->preload("Crypt_AES");
 * $cipher = new Crypt_AES(CRYPT_AES_MODE_ECB); // or: $cipher = Yii::app()->phpseclib->createAES(CRYPT_AES_MODE_ECB);
 *
 * // http://phpseclib.sourceforge.net/ssh/examples.html#logging
 * Yii::app()->phpseclib->preload('Net_SSH2');
 * define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX);
 * $ssh = new Net_SSH2('www.domain.ltd'); // or: $ssh = Yii::app()->phpseclib->createSSH2('www.domain.ltd');
 * // ...
 * echo $ssh->getLog();
 *
 */

class PhpSecLib extends CApplicationComponent
{

    public function init()
    {
        set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . PATH_SEPARATOR . get_include_path());
        // Preload the crypt_random_string function to prevent the Crypt_Random class error (Math/BigInteger.php - line 3064)
        require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Crypt' . DIRECTORY_SEPARATOR . 'Random.php');
        Yii::registerAutoloader(array("PhpSecLib","autoload"));
    }
    
    public static function autoload($class)
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $class) . '.php';
        if(is_file($file))
        {
            require_once($file);
            return true;
        }
        elseif($class === 'File_ASN1_Element')
        {
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'ASN1.php');
            return true;
        }
        elseif($class === 'System_SSH_Agent')
        {
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'System' . DIRECTORY_SEPARATOR . 'SSH_Agent.php');
            return true;
        }
        return false;
    }
    
    public function createAES($mode = "CRYPT_AES_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_AES");
            }
            $mode = constant($mode);
        }
        return new Crypt_AES($mode);
    }
    
    public function createBigInteger($x = 0, $base = 10)
    {
        return new Math_BigInteger($x, $base);
    }
    
    public function createBlowfish($mode = "CRYPT_BLOWFISH_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_Blowfish");
            }
            $mode = constant($mode);
        }
        return new Crypt_Blowfish($mode);
    }
    
    public function createDES($mode = "CRYPT_DES_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_DES");
            }
            $mode = constant($mode);
        }
        return new Crypt_DES($mode);
    }
    
    public function createRC2($mode = "CRYPT_RC2_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_RC2");
            }
            $mode = constant($mode);
        }
        return new Crypt_RC2($mode);
    }
    
    public function createRC4()
    {
        return new Crypt_RC4();
    }
    
    public function createRijndael($mode = "CRYPT_RIJNDAEL_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_Rijndael");
            }
            $mode = constant($mode);
        }
        return new Crypt_Rijndael($mode);
    }
    
    public function createRSA()
    {
        return new Crypt_RSA();
    }
    
    public function createSFTP($host, $port = 22, $timeout = 10)
    {
        return new Net_SFTP($host, $port, $timeout);
    }
    
    public function createSSH2($host, $port = 22, $timeout = 10)
    {
        return new Net_SSH2($host, $port, $timeout);
    }

    public function createSystemSSHAgent()
    {
        return new System_SSH_Agent();
    }
    
    public function createTripleDES($mode = "CRYPT_DES_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_TripleDES");
            }
            $mode = constant($mode);
        }
        return new Crypt_TripleDES($mode);
    }
    
    public function createTwofish($mode = "CRYPT_TWOFISH_MODE_CBC")
    {
        if(is_string($mode))
        {
            if(!defined($mode))
            {
                $this->preload("Crypt_Twofish");
            }
            $mode = constant($mode);
        }
        return new Crypt_Twofish($mode);
    }
    
    public function createX509()
    {
        return new File_X509();
    }
    
    public function hash($type, $text, $key = false)
    {
        $hash = new Crypt_Hash($type);
        $hash->setKey($key);
        return $hash->hash($text);
    }
    
    public function preload($className)
    {
        self::autoload($className);
    }
    
    public function random($length)
    {
        return crypt_random_string($length);
    }
    
}