<?php
/**
 * Brought by https://gist.github.com/mabasic/21d13eab12462e596120
 */
if (!function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '') {
        return app()->basePath().DIRECTORY_SEPARATOR.'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
if(!function_exists('iso_to_mongo_date')){
    function iso_to_mongo_date($iso_8601_tring){
        $ms = (new \DateTime($iso_8601_tring))->getTimeStamp() * 1000;
        return new \MongoDB\BSON\UTCDateTime($ms);
    }
}