<?php

namespace WebAppMaker;

class Config {

    public function readConfigFile($filePath) : array
    {
        $args = [];
        if ($file = fopen($filePath, "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                die($line);
            }
            fclose($file);
        }
        return $args;
    }

    public function configApp($args) : bool
    {
        //Make all web app config
        return false;
    }
}
