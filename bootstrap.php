<?php
/**
 * CSV package
 *
 * @package    Jammit
 * @version    0.1
 * @author     Keith Loy
 * @license    MIT License
 * @copyright  2011-2012 Keith Loy
 */

// Autoloader::add_core_namespace('CSV');

Autoloader::add_classes(array(
    'CSV\\File' => __DIR__.'/classes/file.php',
    'CSV\\Validater' => __DIR__.'/classes/validater.php',
));

/* End of file bootstrap.php */

