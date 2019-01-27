<?php

namespace WebAppMaker;

class Config {

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
        // die(var_dump($files));
        // $options = ['Ice Cream', 'Mixtape', 'Teddy Bear', 'Pizza', 'Puppies'];
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
        return array_filter($config);
    }

    public function buildAppConfiguration($values) : bool
    {
        /*
array(6) {
  ["APP_NAME"]=>
  string(8) "example
"
  ["APP_WEBSERVER"]=>
  string(8) "apache2
"
  ["USER_FOLDER"]=>
  string(11) "/home/user
"
  ["VHOST_FILENAME"]=>
  string(17) "001-example.conf
"
  ["LOCAL_ADDRESS"]=>
  string(13) "example.work
"
  ["DOCUMENT_ROOT"]=>
  string(24) "/var/www/example/public
"
}
*/
        //Make all web app config
        if(!is_array($values)) {
            $this->climateInstance->bold()->red()->out('Configuration values must be an array');
            return false;
        }
        foreach ($values as $key => $value) {
            switch($key) {
                case "APP_NAME":
                break;
                case "APP_WEBSERVER":
                    switch ($value) {
                        case 'apache2':
                            # code...
                            break;
                        case 'nginx':
                            # code...
                            break;
                    }
                break;
                case "USER_FOLDER":
                break;
                case "VHOST_FILENAME":
                break;
                case "LOCAL_ADDRESS":
                break;
                case "DOCUMENT_ROOT":
                break;
            }
        }
        return false;
    }
}
