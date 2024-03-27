<?php
namespace Opencart\Catalog\Controller\Extension\Paysley\Common;
use Opencart\Catalog\Controller\Extension\Paysley\Api\PaysleyApi;
/* Paysley controller
 *
 * @version 1.0.0
 * @date 2020-05-27
 *
 */
class Paysley extends \Opencart\System\Engine\Controller
{
	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code='paysley';

	/**
	 * this variable is payment type
	 *
	 * @var string $payment_type
	 */
	protected $payment_type = 'DB';

	/**
	 * this variable is logo
	 *
	 * @var string $logo
	 */
	protected $logo = '';

	/**
	 * To load the confirm view
	 *
	 * @return  void
	 */
	public function confirmHtml()
	{
		$this->language->load('extension/paysley/payment/paysley');
		$language = $_GET['language'] ?? "";
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = $this->url->link('extension/paysley/common/paysley.getPaymentUrl&language='.$language, '', true);
		$data['payment_gateway_error'] = $this->session->data['payment_gateway_error'] ?? null;
		return $this->load->view('extension/paysley/payment/paysley/confirm', $data);
	}

	/**
	 * Get a payment response then redirect to the payment success page or the payment error page.
	 *
	 * @return  void
	 */
	public function callback()
	{
		$this->load->model('extension/paysley/model_paysley/paysley');
		$this->load->model('checkout/order');
		$this->initApi();
		$payment_details = PaysleyApi::getPaymentDetails(base64_decode($_COOKIE['transaction_id']) ?? null);
		$payment_id = $currency = $amount = null;
		if (!empty($payment_details['transaction'])) {
			$payment_id = $payment_details['transaction']['payment_id'];
			$currency = $payment_details['transaction']['currency'];
			$amount = $payment_details['transaction']['amount'];
		} else {
			self::removeCookie(['transaction_id', 'order_id']);
			$this->response->redirect($_COOKIE['payment_failed_url']);
		}
		$order_id = $_COOKIE['order_id'];
		$order = $this->model_checkout_order->getOrder($order_id);
		if (!empty($order) && !empty($order['payment_method']) && ('Paysley' === $order['payment_method']['name'] || 'paysley' === $order['payment_method']['name'])) {
			$this->model_extension_paysley_model_paysley_paysley->log('OrderId : ' . $order_id);
			$this->model_extension_paysley_model_paysley_paysley->log('Currency : ' . $currency);
			$paysley_order_data = [
				'order_id'		=> $order_id,
				'payment_id'	=> $payment_id,
				'amount'		=> $amount,
				'currency'		=> $currency
			];

			if (!empty($payment_details['transaction'])) {
				$this->model_extension_paysley_model_paysley_paysley->log('callback: update order status to processing');
				$order_status_id = $this->config->get('payment_paysley_processing_status_id');
				$paysley_order_data['result'] = 'success';
			} else {
				$this->model_extension_paysley_model_paysley_paysley->log('callback: update order status to failed');
				$order_status_id = $this->config->get('payment_paysley_failed_status_id');
				$paysley_order_data['result'] = 'failed';
			}
			$comment = 'Payment ID: ' . $payment_id . '<br \>';
			$this->model_extension_paysley_model_paysley_paysley->saveOrder($paysley_order_data);
			$this->model_checkout_order->addHistory($order_id, $order_status_id, $comment, '', true);
			self::removeCookie(['transaction_id', 'order_id']);
			$this->response->redirect($_COOKIE['payment_success_url']);
		}
		self::removeCookie(['transaction_id', 'order_id']);
		echo "Something went wrong"; die;
	}

	/**
	 * Get languages code
	 *
	 * @return string
	 */
	function getLangCode()
	{
		switch (substr($this->session->data['language'], 0, 2)) {
			case 'de':
				$lang_code = "de";
				break;
			default:
				$lang_code = "en";
				break;
		}
		return $lang_code;
	}

	/**
	 * Get a payment type
	 *
	 * @return  string
	 */
	function getPaymentType()
	{
		return $this->payment_type;
	}

	/**
	 * Get a customer ip
	 *
	 * @return  string
	 */
	function getCustomerIp()
	{
		if ($_SERVER['REMOTE_ADDR'] == '::1') {
			return "127.0.0.1";
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Get a template
	 *
	 * @return  string
	 */
	function getTemplate()
	{
		return $this->template;
	}

	/**
	 * Get payment widget at checkout payment page
	 *
	 * @return  string
	 */
	public function getPaymentUrl()
	{
		// Validate cart has products and has stock.
		unset($this->session->data['payment_gateway_error']);
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart', 'language=' . $this->config->get('config_language')));
		}
		$this->load->model('extension/paysley/model_paysley/paysley');
		$this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$this->model_extension_paysley_model_paysley_paysley->log('Paysley Order Info : ' . print_r($order_info, true));
		$currency   = $order_info['currency_code'];
		$amount     = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		// $shipping_charges = !empty($order_info['shipping_method']['cost']) ? $order_info['shipping_method']['cost'] : 0.00; 
		$transaction_id = 'oc-' . $order_id;
		$secret_key =  $this->generateSecretKey();
		$this->model_extension_paysley_model_paysley_paysley->updatePaysleyData($order_id, 'order_id, transaction_id, secret_key', "'$order_id', '$transaction_id', '$secret_key'");
		$token = $this->generateToken($order_id, $currency );
		$this->initApi();
		self::createOrUpdateCustomerOnPaysley($order_info);
		$body = array(
			'reference_number'    => $transaction_id,
			'payment_type'        => $this->payment_type,
			'request_methods'     => ["WEB"],
			'email'               => $order_info['email'] ?? "",
			'mobile_number'       => $order_info['telephone'] ?? "",
			'customer_first_name' => $order_info['firstname'] ?? "",
			'customer_last_name'  => $order_info['lastname'] ?? "",
			'currency'            => $currency,
			'amount'              => (float) $amount,
			// 'shipping_enabled'    => true,
			'cart_items'          => $this->getCartItems(),
			'fixed_amount'        => true,
			'cancel_url'          => $this->url->link('checkout/checkout', '', true),
			'redirect_url'        => $this->url->link('extension/paysley/payment/'.$this->code .'.callback&mp_token='.$token.'&order_id='.$order_id, '', false)
		);
		$this->model_extension_paysley_model_paysley_paysley->log('Payment Parameters : ' . print_r($body, true));
		$post_link = PaysleyApi::generatePosLink($body);
		$this->model_extension_paysley_model_paysley_paysley->log('Post Link : ' . print_r($post_link, true));
		self::removeCookie(['transaction_id', 'payment_failed_url', 'payment_success_url']);
		if (isset($post_link['result']) && $post_link['result'] == 'success') {
			$cookie_data = [
				['key'=>'order_id', 'value'=>$order_id, "time"=>'', 'path'=>''],
				['key'=>'transaction_id', 'value'=>base64_encode($post_link['transaction_id']), "time"=>'', 'path'=>''],
				['key'=>'payment_failed_url', 'value'=>$this->url->link('checkout/checkout', '', true), "time"=>'', 'path'=>''],
				['key'=>'payment_success_url', 'value'=>$this->url->link('checkout/success', '', true), "time"=>'', 'path'=>''],
			];
			self::storeCookie($cookie_data);
			$this->response->redirect($post_link['long_url']);
		}
		$this->redirectError($post_link['error_message']);
	}

	/**
     * redirect to the error message page or the failed message page
     *
     * @param string $error_identifier
     * @return  void
     */
    public function redirectError($error_identifer)
    {
        $this->language->load('extension/paysley/payment/paysley');
		$language = $_GET['language'] ?? "";
        $this->session->data['payment_gateway_error'] = $error_identifer;
        $this->response->redirect($this->url->link('checkout/checkout&language='.$language, '', true));
    }

	/**
	 * this function is generate Secret Key
	 *
	 * @var string $md5
	 */
	protected function generateSecretKey()
	{
		$str = rand();
		return md5($str);
	}

	/**
	 * Init the API class and set the access key.
	 */
	protected function initApi() {
		PaysleyApi::$access_key = $this->config->get('payment_paysley_access_key');
	}

	/**
	 * Get cart items
	 *
	 * @return  array
	 */
	function getCartItems()
	{
		$this->load->model('account/order');
		$this->load->model('catalog/product');
		$this->load->model('localisation/currency');
		$this->load->model('extension/paysley/model_paysley/paysley');
		$cart = $this->model_account_order->getProducts($this->session->data['order_id']);
		$cart_items = array();
		$i = 0;

		// getCurrencyByCode
		$currency_code =  $this->session->data['currency'];
		$currency_data =  $this->model_localisation_currency->getCurrencyByCode($currency_code);
		$currency_value = !empty($currency_data) ? $currency_data['value'] : 0; 
		foreach ($cart as $item) {
			$product = $this->model_catalog_product->getProduct($item['product_id']);
			$cart_items[$i]['qty'] = (int)$item['quantity'];
			$cart_items[$i]['name'] = $item['name'];

			$item_tax = (float)$item['tax'];
			$item_price = (float)$item['price'];

			if ($product['sku'] == '') {
				$sku = '-';
			} else {
				$sku = $product['sku'];
			}

			if (!isset($item_price)) {
				$unit_price = 0;
			} else {
				$price = $item_price + $item_tax;
				$unit_price = $this->currency->format($price, $currency_code, $currency_value, false);
			}
			
			$cart_items[$i]['sku'] = $sku;
			$cart_items[$i]['sales_price'] = $unit_price;
			$cart_items[$i]['unit'] = ['pc'];
			$cart_items[$i]['product_service_id'] = $this->createProductOnPaysley($product);
			$i = $i+1;
		}
		return $cart_items;
	}

	/**
	 * Use this generated token to secure get payment status.
	 * Before call this function make sure paysley_transaction_id and paysley_secret_key already saved.
	 *
	 * @param int    $order_id - Order Id.
	 * @param string $currency - Currency.
	 *
	 * @return string
	 */
	protected function generateToken($order_id, $currency) {
		$this->load->model('extension/paysley/model_paysley/paysley');
		$payment_paysley_data = $this->model_extension_paysley_model_paysley_paysley->getPaymentPaysleyData($order_id);
		$transaction_id = $payment_paysley_data['transaction_id'];
		$secret_key     = $payment_paysley_data['secret_key'];

		return md5((string)$order_id . $currency . $transaction_id . $secret_key );
	}


	/**
	 * Create/Update product on paysley
	 */
	protected function createProductOnPaysley($product)
	{
		$paysley_product_id = "";
		if (empty($product)) {
			return $paysley_product_id;
		}
		$data = [];
		$data['name'] = $product['name'];
		$data['description'] = $product['description'];
		$data['sku'] = $product['sku'];
		$data['category_id'] = $this->checkAndCreateProductCategory($product['product_id']);
		$data['type'] = 'product';
		$data['manage_inventory'] = $product['quantity'] ? 1 : 0;
		$data['unit_in_stock'] = $product['quantity'];
		$data['unit_low_stock'] = 2;
		$data['unit_type'] = 'flat-rate';
		$data['cost'] = $product['price'];
		$data['sales_price'] = $product['price'];

		$existing_products = PaysleyApi::getProducts($product['name']);
		if (!empty($existing_products['result']) && $existing_products['result'] === "success" && !empty($existing_products['product_services'])) {
			$data['id'] = $existing_products['product_services'][0]['id'];
			$product_result = PaysleyApi::updateProduct($data);
		} else {
			$product_result = PaysleyApi::createProduct($data);
		}
		if (!empty($product_result['result']) && 'success' === $product_result['result']) {
			$paysley_product_id = !empty($product_result['product_and_service']) ? $product_result['product_and_service']['id'] : $product_result['id'];
		}
		// return 1107;
		return $paysley_product_id;
	}


	/**
	 * Check if category created on paysley.If category is not created then it will create category on paysley.
	 * It will return CategoryId 
	 */
	public function checkAndCreateProductCategory($product_id)
	{
		if (empty($product_id)) {
			return "";
		}

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$categories = $this->model_catalog_product->getCategories($product_id);

		$productCategory = !empty($categories[0]['category_id']) ? $this->model_catalog_category->getCategory($categories[0]['category_id']) : [];
		if (!empty($productCategory)) {
			$categoryResult = PaysleyApi::categoryList($productCategory['name']);
			if (!empty($categoryResult['result']) && 'success' === $categoryResult['result']) {
				if (!empty($categoryResult['categories'])) {
					return $categoryResult['categories'][0]['id'];
				}
				$categoryCreateResult = PaysleyApi::createCategory(['name' => $productCategory['name']]);
				if (!empty($categoryCreateResult)) {
					return $categoryCreateResult['id'];
				}
			}
		}
		return "";
	}


	/**
	 * Create/Update Customer on paysley
	 */
	public static function createOrUpdateCustomerOnPaysley($order_info)
	{
		$customer_paysley_id = null;
		$check_if_customer_exist_on_paysley_result = PaysleyApi::customerList($order_info['email']);
		if (!empty($check_if_customer_exist_on_paysley_result['result']) && 'success' === $check_if_customer_exist_on_paysley_result['result']) {
			$customer_data_to_update = [];
			// Customer billing information details
			$customer_data_to_update['email']         = $order_info['email'] ?? "";
			$customer_data_to_update['mobile_no']     = $order_info['telephone'] ?? "";
			$customer_data_to_update['first_name']    = $order_info['firstname'] ?? "";
			$customer_data_to_update['last_name']     = $order_info['lastname'] ?? "";
			$customer_data_to_update['company_name']  = $order_info['payment_company'] ?? "";
			$customer_data_to_update['listing_type']  = 'individual';
			$customer_data_to_update['address_line1'] = $order_info['payment_address_1'] ?? "";
			$customer_data_to_update['address_line2'] = $order_info['payment_address_2'] ?? "";
			$customer_data_to_update['city'] 		  = $order_info['payment_city'] ?? "";
			$customer_data_to_update['state'] 		  = $order_info['payment_zone'] ?? "";
			$customer_data_to_update['postal_code']   = $order_info['payment_postcode'] ?? "";
			$customer_data_to_update['country_iso']   = $order_info['payment_iso_code_3'] ?? "";

			if (!empty($check_if_customer_exist_on_paysley_result['customers'])) {
				$customer_data_index = array_search($order_info['email'], array_column($check_if_customer_exist_on_paysley_result['customers'], 'email'));				
				$customer_data_to_update['customer_id'] = $customer_paysley_id = $check_if_customer_exist_on_paysley_result['customers'][$customer_data_index]['customer_id'] ?? null;
			}		
			if (!empty($customer_paysley_id)) {
				$update_customer_on_paysley_result = PaysleyApi::updateCustomer($customer_data_to_update);		
				if (!empty($update_customer_on_paysley_result['result']) && 'success' === $update_customer_on_paysley_result['result']) {
				}
			} else {
				$create_customer_on_paysley_result = PaysleyApi::createCustomer($customer_data_to_update);

				if (!empty($create_customer_on_paysley_result['result']) && 'success' === $create_customer_on_paysley_result['result']) {
					$customer_paysley_id = $create_customer_on_paysley_result['customer_id'];
				}
			}
		}
		return $customer_paysley_id;
	}

	/** 
	 * Function to create the cookie
	 * @param $cookie_detail    : cookie data to store
	 */
	public static function storeCookie ($cookie_details = []) {
		if (!empty($cookie_details)) {
			array_map(function ($cookie_detail) {
				$time = !empty($cookie_detail['time']) ? $cookie_detail['time'] : time() + (86400 * 30); 
				$path = !empty($cookie_detail['path']) ? $cookie_detail['path'] : "/"; 
				//set the cookie				
				setcookie($cookie_detail['key'], $cookie_detail['value'], $time, $path); 	
			}, $cookie_details);
		}
	}

	/** 
	 * Function to remove the cookie if exists
	 * @param $cookie_names    : cookie names to delete
	 */
	public static function removeCookie ($cookie_names = []) {
		if (!empty($cookie_names)) {
			array_map(function ($cookie_name) {
				if (isset($cookie_name)) {
					unset($_COOKIE[$cookie_name]); 	
				}
			}, $cookie_names);
		}
	}
}
