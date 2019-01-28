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

    public function buildAppConfiguration($values) : bool
    {
        //Make all web app config
        if(!is_array($values)) {
            $this->climateInstance->bold()->red()->out('Configuration values must be an array');
            return false;
        }

        //Step by step instead of loop through parameters values
        //Create app dev folder
        if($this->noValueCheck($values['USER_FOLDER']) && is_dir($values['USER_FOLDER'])) {
            if($this->noValueCheck($values['APP_FOLDER']) && is_dir($values['APP_FOLDER'])) {
                echo("mkdir -p ".$values['APP_FOLDER']);
                // execOrFail("mkdir -p ".$values['APP_FOLDER']);
            }
        }
        //Create vhost
        //Fill vhost
        //Edit /etc/hosts
        //a2ensite
        //symlink dev folder and apache RootDirectory folder  /var/www/uwithi/public

        return false;
    }
}
