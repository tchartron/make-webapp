<?php
namespace WebAppMaker;

trait Wrapper {

    function execOrFail($command) {
        try {
            exec($command, $output);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        // return (!empty($output)) ? $output : true;
        return $output;
    }

    function noValueCheck($value) : bool
    {
        return ($value !== "" && $value !== 0 && $value !== null);
    }
}
