<?php
namespace Opencart\Catalog\Controller\Extension\Paysley\Payment;
use Opencart\Catalog\Controller\Extension\Paysley\Common\Paysley as CommonPaysley;
/* Paysley payment controller
 *
 * @version 1.0.01
 * @date 2018-09-26
 *
 */
class Paysley extends CommonPaysley
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
