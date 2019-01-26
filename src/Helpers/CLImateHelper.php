<?php
namespace Helpers;

// use League\CLImate\CLImate;

class CLImateHelper {

    // public $climateInstance = new CLImate;

    public function customOutput($climateInstance, ...$args) {
        foreach($args as $arg) {
            switch ($arg) {
                case 'backgroundLightGreen':
                    $call .= 'backgroundLightGreen()';
                    break;
                case 'bold':
                    $call .= 'bold()';
                    break;
                case 'black':
                    $call .= 'black()';
                    break;

                default:
                    $call = false;
                    break;
            }
        }
$climate->backgroundLightGreen()->bold()->black()->out('This prints to the terminal.');

        return $climateInstance.$call;
    }
}
