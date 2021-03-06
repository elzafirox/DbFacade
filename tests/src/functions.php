<?php
/**
 * Functions for testing and evaluating NewFreetag.
 *
 * All functions defined here are namespaced <code>NewFreetag\Testing</code>.
 */

namespace DbFacade\Tests;



function pre_r($var)
{
    return '<pre>' . print_r($var, "noecho") . "</pre>\n";
}


function pre_dump($var)
{
    ob_start();
    var_dump($var);
    return '<pre>' . ob_get_clean() . "</pre>\n";
}



function pretty($code)
{
    return '<pre class="prettyprint linenums">'
          . $code
          . "</pre>\n";
}

function pretty_print($code)
{
    echo pretty($code);
}


function testUnit($title, $content = '', $code='')
{
	$result = '<h3>' . $title . "</h3>\n" . codeBox($content, $code, false);
	echo $result;
}


function codeBox($content = '', $code='', $echo = true)
{
    $result = '';
    if ($code) {
        $result .= '<code><pre>' . $code . "</pre></code>\n";
    }

    if ($content) {
        $result .= '<div class="test result">' . $content . "</div>\n";
    }
    if ($echo) {
        echo $result;
    }
    else {
        return $result;
    }

}





/**
 * @return \PDO
 * @throws \LogicException
 */
function getPdoConnection($db_driver, $db_host, $db_name, $db_user, $db_pass, $charset = 'utf8', $persistent = true)
{
    if (!class_exists('\PDO')) {
        throw new \LogicException("Class \PDO does not exist, is ADOdb loaded?");
    }
    return new \PDO("$db_driver:host=$db_host;dbname=$db_name", $db_user, $db_pass);
}


/**
 * @return \ADOConnection
 * @throws \LogicException
 * @throws \RuntimeException
 */
function getDatabaseADOConnection($db_driver, $db_host, $db_name, $db_user, $db_pass, $charset = 'utf8', $persistent = true)
{
    if (!function_exists('ADONewConnection')) {
        throw new \LogicException("Function \ADONewConnection does not exist, is ADOdb loaded?");
    }

    $db = \ADONewConnection($db_driver);

    if (!$connecting = $persistent
    ? $db->PConnect($db_host, $db_user, $db_pass, $db_name)
    : $db->Connect($db_host, $db_user, $db_pass, $db_name)) {
        throw new \RuntimeException("Connection to database failed.");
    }

    $db->Execute("set names '" .$charset. "'");

    return $db;
}
