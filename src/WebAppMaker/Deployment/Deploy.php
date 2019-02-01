<?php
namespace WebAppMaker\Deployment;

use \WebAppMaker\Wrapper;

class Deployment {
    use Wrapper;

    private $climateInstance;

    public function __construct($climateInstance)
    {
        $this->climateInstance = $climateInstance;
    }

    public function deployFolderToLocalWebServer() : bool
    {

    }

    public function deployFolderToRemoteWebServer() : bool
    {

    }
}
