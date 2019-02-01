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
        //Edit /etc/hosts
        //symlink dev folder and apache RootDirectory folder  /var/www/uwithi/public
        //restart webserver
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
                /////////////
                //APACHE 2 //
                /////////////
                case 'apache2':
                        //Create vhost file
                        if($this->noValueCheck($configValues['VHOST_FILENAME'])) {
                            $this->climateInstance->backgroundLightGreen()->bold()->black()->out('Creating vhost file  : '.$configValues['VHOST_FILENAME'].' in /etc/apache2/sites-available/');
                            $this->execOrFail("touch /etc/apache2/sites-available/".trim($configValues['VHOST_FILENAME']));
                        }
                        //Fill vhost
                        $vhostPath = realpath(__DIR__."/../../../config/vhost/apache2");
                        $vhostPath = rtrim($vhostPath, "/");
                        $files = glob($vhostPath."/*");
                        if(empty($files)) {
                            $this->climateInstance->bold()->red()->out("No config files found in ".$vhostPath);
                            exit();
                        }
                        $input = $this->climateInstance->radio('Please send me one of the following:', $files);
                        $vhostFile = $input->prompt();
                        if(file_exists($vhostFile)) {
                            $vhostContent = file_get_contents($vhostFile);
                            //Replace vars in template
                            preg_match_all("/{{[a-zA-Z_\s]+}}/", $vhostContent, $matches);
                            if(is_array($matches[0])) {
                                //Extract keys from template vars
                                $keys = array_map(function($e) {
                                    return str_replace(["{{ ", " }}"], "", $e);
                                }, $matches[0]);
                                foreach ($keys as $value) {
                                    //Replace all {{ VAR }} with proper value
                                    $vhostFinalContent = str_replace($matches[0], trim($configValues[$value]), $vhostContent);
                                }
                                //Fill the file
                                // die(var_dump($vhostFinalContent));
                                file_put_contents("/etc/apache2/sites-available/".trim($configValues['VHOST_FILENAME'], $vhostFinalContent));
                            }
                        }
                break;
                //////////
                //NGINX //
                //////////
                case 'nginx':
                break;
                default:
                    $this->climateInstance->bold()->red()->out('Web server : '.$configValues['APP_WEBSERVER'].' not known');
                break;
            }
        }
        /////////////////
        //COMMON STEPS //
        /////////////////
        //Edit /etc/hosts
        if($this->noValueCheck($configValues['VHOST_LOCAL_ADDRESS'])) {
            if(file_exists("/etc/hosts")) {
                $hostContent = file_get_contents("/etc/hosts");
                $newHost = "#".trim($configValues['VHOST_LOCAL_ADDRESS']);
                $newHost .= "127.0.0.1    ".trim($configValues['VHOST_LOCAL_ADDRESS']);
                file_put_contents("/etc/hosts", $newHost, FILE_APPEND);
            }
        }
        //a2ensite
        $this->execOrFail("a2ensite ".trim($configValues['VHOST_FILENAME']));
        //symlink dev folder and apache RootDirectory folder  /var/www/uwithi/public
        $this->execOrFail("ln -s ".trim($configValues['APP_FOLDER'])." ".trim($configValues['WEB_SERVER_APP_FOLDER']));
        //restart apache
        if(trim($configValues['APP_WEBSERVER']) == "apache2") {
            $this->execOrFail("/etc/init.d/apache2 restart");
        } elseif ($configValues['APP_WEBSERVER'] == "nginx") {
            # code...
        }

        return true;
    }

    public function removeAppConfiguration($configValues) : bool
    {
        return true;
    }

    public function pullComposerPackage($packageName, $projectName) : bool
    {
        //Pull laravel
        //composer create-project --prefer-dist laravel/laravel $projectName

        return true;
    }
}
