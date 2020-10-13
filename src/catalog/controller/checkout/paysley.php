<?php
/* Paysley checkout controller
 *
 * @version 1.0.0
 * @date 2020-05-27
 *
 */
include_once(dirname(__FILE__) . '/../paysley/paysley.php');

class ControllerCheckoutPaysley extends ControllerPaysley {

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code = "paysley";
}
