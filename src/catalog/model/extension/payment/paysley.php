<?php
/* Paysley model
 *
 * @version 1.0.01
 * @date 2018-09-26
 *
 */
include_once(dirname(__FILE__) . '/../../paysley/paysley.php');

class ModelExtensionPaymentPaysley extends ModelPaysleyPaysley
{
	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code = 'paysley';

	/**
	 * this variable is title
	 *
	 * @var string $title
	 */
	protected $title = 'FRONTEND_PM_PAYSLEY';

	/**
	 * this variable is logo
	 *
	 * @var string $logo
	 */
	protected $logo = 'paysley.png';
}
