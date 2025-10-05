<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']        = 'API demo (pi1)';        // Name des Plugins im UI
$string['modulename']        = 'API demo (pi1)';        // historisch, wird noch verwendet
$string['modulenameplural']  = 'API demos (pi1)';

// Capability-Beschriftungen (ohne "mod/")
$string['pi1:addinstance']   = 'Add a new API demo activity (pi1)';
$string['pi1:view']          = 'View API demo (pi1)';


$string['query'] = 'Search parameter';
$string['query_help'] = 'Enter the value your activity should query the public API with (e.g. a city name like "Berlin").';

// Texte für view.php
$string['pi1_rawjson']      = 'Raw API response';
$string['pi1_pretty']       = 'Current weather';
$string['pi1_citylabel']    = 'City: {$a}';
$string['pi1_temp']         = 'Temperature: {$a}';
$string['pi1_wind']         = 'Wind: {$a}';
$string['pi1_noplacefound'] = 'No place found for "{$a}".';
$string['pi1_noweather']    = 'No current weather data received.';
$string['pi1_form_q'] = 'City or search term';
$string['pi1_search'] = 'Search';
