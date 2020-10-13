<?php
/* Paysley controller in admin
 *
 * @version 1.0.0
 * @date 2020-05-27
 *
 */

class ControllerExtensionPaymentPaysley extends Controller {

	/**
	 * this variable is the error
	 *
	 * @var array $error
	 */
	private $error = array();

	/**
	 * this variable is the keys
	 *
	 * @var array $keys
	 */
	private $keys = array(
		'title',
		'description',
		'access_key',
		'status',
		'geo_zone_id',
		'sort_order'
	);

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	private $code = 'paysley';


	/**
	 * this function is the constructor of ControllerExtensionPaymentPaysley class
	 *
	 * @return  void
	 */
	public function index() {
		$this->load->language('extension/payment/' . $this->code);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/payment/paysley');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$lang_id = $this->model_extension_payment_paysley->getLangId();

			$this->load->model('localisation/order_status');

			$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
			foreach ($data['order_statuses'] as $key => $value) {
				if (isset($lang_id['en'])) {
					if ($this->config->get('config_language_id') == $lang_id['en']) {
						if ($value['name'] == "Processing") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Failed") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Refunded") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['ar'])) {
					if ($this->config->get('config_language_id') == $lang_id['ar']) {
						if ($value['name'] == "المعالجة") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "فشل") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "تم استردادها") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['de'])) {
					if ($this->config->get('config_language_id') == $lang_id['da']) {
						if ($value['name'] == "Behandling") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Mislykkedes") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Refuderet") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['de'])) {
					if ($this->config->get('config_language_id') == $lang_id['de']) {
						if ($value['name'] == "Verarbeitung") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Gescheitert") {
							$order_statuses['payment_' . $this->code . '_failed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Gutschrift") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['es'])) {
					if ($this->config->get('config_language_id') == $lang_id['es']) {
						if ($value['name'] == "Procesamiento") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Falló") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Reembolsado") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['fi'])) {
					if ($this->config->get('config_language_id') == $lang_id['fi']) {
						if ($value['name'] == "Käsittely") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Epäonnistui") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Palautetaan") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['fr'])) {
					if ($this->config->get('config_language_id') == $lang_id['fr']) {
						if ($value['name'] == "Traitement") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Échec") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Remboursé") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['id'])) {
					if ($this->config->get('config_language_id') == $lang_id['id']) {
						if ($value['name'] == "Pengolahan") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Gagal") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Dikembalikan") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['it'])) {
					if ($this->config->get('config_language_id') == $lang_id['it']) {
						if ($value['name'] == "Elaborazione") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Fallito") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Rimborso") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['ja'])) {
					if ($this->config->get('config_language_id') == $lang_id['ja']) {
						if ($value['name'] == "処理中") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "失敗しました") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "返金済み") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['ko'])) {
					if ($this->config->get('config_language_id') == $lang_id['ko']) {
						if ($value['name'] == "처리 중") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "실패했습니다") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "환불됨") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['nl'])) {
					if ($this->config->get('config_language_id') == $lang_id['nl']) {
						if ($value['name'] == "Verwerking") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Mislukt") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Terugbetaald") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['pl'])) {
					if ($this->config->get('config_language_id') == $lang_id['pl']) {
						if ($value['name'] == "Przetwarzanie") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Nie powiodło się") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Zwrot") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['pt'])) {
					if ($this->config->get('config_language_id') == $lang_id['pt']) {
						if ($value['name'] == "Processando") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Falhou") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Reembolsado") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['ru'])) {
					if ($this->config->get('config_language_id') == $lang_id['ru']) {
						if ($value['name'] == "Обработка") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Не удалось") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Возвращено") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['sv'])) {
					if ($this->config->get('config_language_id') == $lang_id['sv']) {
						if ($value['name'] == "Bearbetning") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Misslyckades") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Återbetalas") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['tr'])) {
					if ($this->config->get('config_language_id') == $lang_id['tr']) {
						if ($value['name'] == "İşleme") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "Başarısız") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "İade edilir") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
				if (isset($lang_id['zh'])) {
					if ($this->config->get('config_language_id') == $lang_id['zh']) {
						if ($value['name'] == "正在处理") {
							$order_statuses['payment_' . $this->code . '_processing_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "失败") {
							$order_statuses['payment_' . $this->code . '_filed_status_id'] = $value['order_status_id'];
						}
						if ($value['name'] == "已退款") {
							$order_statuses['payment_' . $this->code . '_refund_status_id'] = $value['order_status_id'];
						}
					}
				}
			}

			$config_post = array_merge($this->request->post, $order_statuses);
			$this->model_setting_setting->editSetting('payment_' . $this->code, $config_post);

			$this->session->data['success'] = $this->language->get('BACKEND_CH_SUCCESS');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['BACKEND_CH_EDIT'] = $this->language->get('BACKEND_CH_EDIT');

		$data['BACKEND_CH_TITLE'] = $this->language->get('BACKEND_CH_TITLE');
		$data['BACKEND_CH_DESCRIPTION'] = $this->language->get('BACKEND_CH_DESCRIPTION');
		$data['BACKEND_CH_ACCESS_KEY'] = $this->language->get('BACKEND_CH_ACCESS_KEY');
		$data['BACKEND_CH_STATUS'] = $this->language->get('BACKEND_CH_STATUS');

		$data['custom_fields'] = $this->model_extension_payment_paysley->getCustomFields();
		$data['custom_field_values'] = $this->model_extension_payment_paysley->getCustomFieldValues();

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('BACKEND_CH_PAYMENT'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/' . $this->code, 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/payment/' . $this->code, 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		foreach ($this->keys as $value) {
			$key = 'payment_' . $this->code . '_' . $value;
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/' . $this->code, $data));
	}

	/**
	 * to install the payment method
	 *
	 * @return  void
	 */
	public function install() {
		$this->load->language('extension/payment/' . $this->code);

		$this->load->model('setting/setting');
		$this->load->model('extension/payment/paysley');
		$this->model_extension_payment_paysley->addCustomOrderStatuses();
		$this->model_extension_payment_paysley->install();
	}

	/**
	 * to validate another field in the payment method configuration
	 *
	 * @return  boolean
	 */
	public function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/paysley')) {
			$this->error['warning'] = $this->language->get('ERROR_PERMISSION');
			return false;
		}
		if (empty($this->request->post['payment_paysley_access_key'])) {
			$this->error['warning'] = $this->language->get('BACKEND_CH_ACCESS_KEY') . ' ' . $this->language->get('ERROR_MANDATORY');
			return false;
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
