<?php
require 'Str.php';

define('CONTROLLER_SUFFIX', 'Controller');

/**
 * Generate a controller class name
 * <code>
 *      $class = controller_class_for_name("Big Thing"); // returns BigThingController
 *      $class = controller_class_for_name("big_thing"); // returns BigThingController
 *      $class = controller_class_for_name("BigThingController"); // returns BigThingController
 * </code>
 *
 * @param string $name
 * @return string
 */
function controller_class_for_name($name) {
	$class_name = ucfirst(str_camel(str_proper($name)));
	if (substr($class_name, -strlen(CONTROLLER_SUFFIX)) != CONTROLLER_SUFFIX) $class_name .= CONTROLLER_SUFFIX;
	return $class_name;
}