<?php
namespace Opencart\Catalog\Model\Extension\Paysley\Payment;
/* Paysley model
 *
 * @version 1.0.01
 * @date 2018-09-26
 *
 */
class Paysley extends \Opencart\System\Engine\Model
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

	/**
	 * Get the Method
	 * this funtion is the OpenCart funtion
	 *
	 * @param string $address
	 * @param int $total
	 * @return  array
	 */
	public function getMethods($address) { 
		unset($this->session->data['payment_gateway_error']);
		$this->language->load('extension/paysley/payment/paysley');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_' . $this->code . '_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		if ($this->config->get('payment_' . $this->code . '_total') > 0) {
			$status = false;
		} elseif (!$this->config->get('payment_' . $this->code . '_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		$method_data = array();
		if ($status) {
			$option_data['paysley'] = [
				'code' => 'paysley.paysley',
				'name' => $this->language->get($this->title)
			];
			$method_data = array(
				'code'       	=> $this->code,
				'name'      	=> $this->language->get($this->title),
				'logo'			=> $this->getLogo(),
				'option'        => $option_data,
				'terms'		 	=> '',
				'sort_order' 	=> $this->config->get('payment_' . $this->code . '_sort_order')
			);
		}
		return $method_data;
	}

	/**
	 * get the payment method logo
	 *
	 * @return string
	 */
	public function getLogo() {

		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/image/paysley/' . $this->logo)) {
			$logo_html = '<img src="catalog/view/theme/' . $this->config->get('config_template') . '/image/paysley/' . $this->logo . '" border="0" style="height:35px;">';
		} else {
			$logo_html = '<img src="catalog/view/theme/default/image/paysley/' . $this->logo . '" border="0" style="height:35px;">';
		}
		return $logo_html;
	}
}
