<?php
/* Paysley payment controller
 *
 * @version 1.0.01
 * @date 2018-09-26
 *
 */
include_once(dirname(__FILE__) . '/../../paysley/paysley.php');

class ControllerExtensionPaymentPaysley extends ControllerPaysley
{

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code = 'paysley';

	/**
	 * this function is the constructor of ControllerPaysley class
	 *
	 * @return  void
	 */
	public function index()
	{

		return $this->confirmHtml();
	}
}
