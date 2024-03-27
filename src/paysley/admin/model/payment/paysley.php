<?php
namespace Opencart\Admin\Model\Extension\Paysley\Payment;
/**
 * Class Paysley
 *
 * @package Opencart\Admin\Controller\Extension\Paysley\Payment
 */

 class Paysley extends \Opencart\System\Engine\Model {

	/**
	 * Get language ID
	 *
	 * @return array
	 */
	public function getLangId() {
		$this->load->model('localisation/language');

		$langs = $this->model_localisation_language->getLanguages();

		$lang_id['en'] = 0;
		$lang_id['de'] = 0;
		foreach ($langs as $lang) {
			if (substr($lang['code'], 0, 2) == 'en') {
				$lang_id['en'] = $lang['language_id'];
			}
			if (substr($lang['code'], 0, 2) == 'de') {
				$lang_id['de'] = $lang['language_id'];
			}
		}
		return $lang_id;
	}

	/**
	 * Get custom fields
	 *
	 * @return array
	 */
	public function getCustomFields() {
		$language_id = (int)$this->config->get('config_language_id');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_description WHERE language_id = '" . $language_id . "'");

		return $query->rows;
	}

	/**
	 * Get custom fields values
	 *
	 * @return array
	 */
	public function getCustomFieldValues() {
		$language_id = (int)$this->config->get('config_language_id');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "custom_field_value_description WHERE language_id = '" . $language_id . "'");

		return $query->rows;
	}

	/**
	 * Adding Paysley order statuses into the opencart database
	 *
	 * @return void
	 */
	public function addCustomOrderStatuses() {
		$lang_id = $this->getLangId();
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$is_add_processing = true;
		$is_add_refund = true;
		$is_add_failed = true;

		foreach ($data['order_statuses'] as $key => $value) {
			if ($this->config->get('config_language_id') == $lang_id['en'] && $value['name'] == "Processing") {
				$is_add_processing = false;
			}
			if ($this->config->get('config_language_id') == $lang_id['de'] && $value['name'] == "Verarbeitung") {
				$is_add_processing = false;
			}
			if ($this->config->get('config_language_id') == $lang_id['en'] && $value['name'] == "Failed") {
				$is_add_failed = false;
			}
			if ($this->config->get('config_language_id') == $lang_id['de'] && $value['name'] == "Gescheitert") {
				$is_add_failed = false;
			}
			if ($this->config->get('config_language_id') == $lang_id['en'] && $value['name'] == "Refunded") {
				$is_add_refund = false;
			}
			if ($this->config->get('config_language_id') == $lang_id['de'] && $value['name'] == "Gutschrift") {
				$is_add_refund = false;
			}
		}

		if ($this->config->get('config_language_id') == $lang_id['en'] || $this->config->get('config_language_id') == $lang_id['de']) {
			if ($is_add_processing) {
				if ($lang_id['en'] > 0) {
					$data['order_status'][$lang_id['en']]['name'] = "Processing";
				}
				if ($lang_id['de'] > 0) {
					$data['order_status'][$lang_id['de']]['name'] = "Verarbeitung";
				}
				$this->model_localisation_order_status->addOrderStatus($data);
			}

			if ($is_add_failed) {
				if ($lang_id['en'] > 0) {
					$data['order_status'][$lang_id['en']]['name'] = "Failed";
				}
				if ($lang_id['de'] > 0) {
					$data['order_status'][$lang_id['de']]['name'] = "Gescheitert";
				}
				$this->model_localisation_order_status->addOrderStatus($data);
			}

			if ($is_add_refund) {
				if ($lang_id['en'] > 0) {
					$data['order_status'][$lang_id['en']]['name'] = "Refunded";
				}
				if ($lang_id['de'] > 0) {
					$data['order_status'][$lang_id['de']]['name'] = "Gutschrift";
				}
				$this->model_localisation_order_status->addOrderStatus($data);
			}
		}
	}

	/**
	 * Install the Paysley module
	 *
	 * @return void
	 */
	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payment_paysley_orders` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`order_id` INT(11) NOT NULL,
					`payment_id` VARCHAR(32) NOT NULL,
					`amount` DECIMAL(15,4) NOT NULL,
					`currency` VARCHAR(3) NOT NULL,
					`result` VARCHAR(8) NOT NULL,
					PRIMARY KEY (`id`)
			) ENGINE=MyISAM; ");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payment_paysley_data` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`order_id` INT(11) NOT NULL,
					`secret_key` VARCHAR(32) NOT NULL,
					`transaction_id` VARCHAR(32) NOT NULL,
					PRIMARY KEY (`id`)
			) ENGINE=MyISAM; ");
	}
}
