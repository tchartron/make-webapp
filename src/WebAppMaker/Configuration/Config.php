<?php
namespace WebAppMaker\Configuration;

use \WebAppMaker\Wrapper;

class Config {
    use Wrapper;
    // public const __ROOT_DIR__ = __DIR__;

    private $allowedExtension = ["conf", "appconf"];
    private $climateInstance;

    public function __construct($climateInstance)
    {
        $this->climateInstance = $climateInstance;
    }

    public function selectConfigFile($configPath) : string
    {
        //trim trailing slash
        $configPath = rtrim($configPath, "/");
        $brace = "{".implode(",", $this->allowedExtension)."}"; //{jpg,gif,png}
        $files = glob("$configPath/*.$brace", GLOB_BRACE);
        // $this->climateInstance->backgroundLightGreen()->bold()->black()->out("$configPath/*.$brace");
        if(empty($files)) {
            $this->climateInstance->bold()->red()->out("No config files found in ".$configPath);
            exit();
        }
        $input = $this->climateInstance->radio('Please send me one of the following:', $files);
        $response = $input->prompt();
        return $response;
    }

    public function readConfigFile($filePath) : array
    {
        $config = [];
        if ($file = fopen($filePath, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                //convert all values to key value pair
                try {
                    $explodeLine = explode("=", $line);
                    //push to array
                    if(!empty($explodeLine[0])) {
                        $config[$explodeLine[0]] = $explodeLine[1];
                    }
                } catch (Exception $ex) {
                    $this->climateInstance->bold()->red()->out("Failed to read config file ".$ex->getMessage());
                    exit();
                }
            }
            fclose($file);
        }
        //Filter to get rid of empty values
        return array_filter($config);
    }

    public function buildAppConfiguration($configValues) : bool
    {
        //Make all web app config
        if(!is_array($configValues)) {
            $this->climateInstance->bold()->red()->out('Configuration values must be an array');
            return false;
        }

        //Step by step instead of loop through parameters values
        //Create app dev folder APP_FOLDER
        //set owner and groups to APP_FOLDER_OWNER:APP_FOLDER_GROUP
        //check APP_WEBSERVER
        //create vhost VHOST_FILENAME
        //fill VHOST_FILENAME
        //
        /**
         * IMPORTANT NOTE :
         * Trim values is very important as it appear to have a CLRF/LF or a space and is_dir does not like this
         */
        if($this->noValueCheck($configValues['USER_FOLDER']) && is_dir(trim($configValues['USER_FOLDER']))) {
            if($this->noValueCheck($configValues['APP_FOLDER'])) {
                // echo("mkdir -p ".$configValues['APP_FOLDER']);
                $this->climateInstance->backgroundLightGreen()->bold()->black()->out('Creating app folder : '.$configValues['APP_FOLDER']);
                $this->execOrFail("mkdir -p ".trim($configValues['APP_FOLDER']));
                $this->climateInstance->backgroundLightGreen()->bold()->black()->out('Changing app folder rights to : '.$configValues['APP_FOLDER_OWNER'].':'.$configValues['APP_FOLDER_GROUP']);
                $this->execOrFail("chown -Rv ".trim($configValues['APP_FOLDER_OWNER']).":".trim($configValues['APP_FOLDER_GROUP'])." ".trim($configValues['APP_FOLDER']));
            } else {
                $this->climateInstance->bold()->red()->out($configValues['APP_FOLDER'].' APP_FOLDER Incorrect value');
            }
        } else {
            $this->climateInstance->bold()->red()->out($configValues['USER_FOLDER'].' USER_FOLDER Incorrect value or not a directory');
        }

        /////////////////////////////////////////
        //WEB SERVER SPECIFIQUE CONFIGURATIONS //
        /////////////////////////////////////////
        if($this->noValueCheck($configValues['APP_WEBSERVER'])) {
            switch(trim($configValues['APP_WEBSERVER'])) {
                case 'apache2':
                        //Create vhost file
                        if($this->noValueCheck($configValues['VHOST_FILENAME'])) {
                            $this->climateInstance->backgroundLightGreen()->bold()->black()->out('Creating vhost file  : '.$configValues['VHOST_FILENAME'].' in /etc/apache2/sites-available/');
                            $this->execOrFail("touch  /etc/apache2/sites-available/".trim($configValues['VHOST_FILENAME']));
                        }
                        //Fill vhost
                        //trim trailing slash
                        // die(var_dump(TEST)); //Access constant
                        $vhostPath = realpath(__DIR__."/../../../config/vhost/apache2");
                        // $this->climateInstance->bold()->red()->out($vhostPath);
                        // die(var_dump($vhostPath));
                        $vhostPath = rtrim($vhostPath, "/");
                        // $brace = "{".implode(",", $this->allowedExtension)."}"; //{jpg,gif,png}
                        // die(realpath(__DIR__."../../config/vhost/apache2/"));
                        $files = glob($vhostPath."/*");
                        // $this->climateInstance->backgroundLightGreen()->bold()->black()->out("$vhostPath/*.$brace");
                        if(empty($files)) {
                            $this->climateInstance->bold()->red()->out("No config files found in ".$vhostPath);
                            exit();
                        }
                        $input = $this->climateInstance->radio('Please send me one of the following:', $files);
                        $vhostFile = $input->prompt();
                        if(file_exists($vhostFile)) {
                            $vhostContent = file_get_contents($vhostFile);
                            //Replace vars in template
                            //MAKE THIS A METHOD ?
                            preg_match_all("/{{[a-zA-Z_\s]+}}/", $vhostContent, $matches);
                            // die(var_dump($matches));
                            /*
                            *array(1) {
  [0]=>
  array(7) {
    [0]=>
    string(23) "{{ VHOST_SERVERADMIN }}"
    [1]=>
    string(25) "{{ VHOST_LOCAL_ADDRESS }}"
    [2]=>
    string(25) "{{ VHOST_LOCAL_ADDRESS }}"
    [3]=>
    string(25) "{{ VHOST_DOCUMENT_ROOT }}"
    [4]=>
    string(25) "{{ VHOST_DOCUMENT_ROOT }}"
    [5]=>
    string(21) "{{ VHOST_ERROR_LOG }}"
    [6]=>
    string(22) "{{ VHOST_ACCESS_LOG }}"
  }
}

                             */
                            if(is_array($matches)) {
                                foreach ($matches as &$value) {
                                    //Replacing mustaches with nothing
                                    $value = str_replace(["{{ ", " }}"], "", $value);
                                    // $value = str_replace($value, $configValues[$value], $value);
                                }
                                foreach ($matches as &$value) {
                                    $tempValue = $value;
                                    die(var_dump($tempValue));
                                    $value = str_replace($tempValue, $configValues[$value], $value);
                                }
                                die(var_dump($matches));
                            }
                        }
                        // return $response;
                        // die($vhostFile);
                break;
                case 'nginx':
                break;
                default:
                    $this->climateInstance->bold()->red()->out('Web server : '.$configValues['APP_WEBSERVER'].' not known');
                break;
            }
        }


        //Edit /etc/hosts
        //a2ensite
        //symlink dev folder and apache RootDirectory folder  /var/www/uwithi/public
        //reload apache

        return false;
    }
}
