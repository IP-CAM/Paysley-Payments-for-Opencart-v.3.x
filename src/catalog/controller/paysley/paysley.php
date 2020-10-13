<?php

/* Paysley controller
 *
 * @version 1.0.0
 * @date 2020-05-27
 *
 */

include_once(dirname(__FILE__) . '/paysleyapi.php');

class ControllerPaysley extends Controller
{
	/**
	 * this variable is Version
	 *
	 * @var string $version
	 */
	protected $version = '1.0.0';

	/**
	 * this variable is Code
	 *
	 * @var string $code
	 */
	protected $code='';

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
	 * this function is the constructor of ControllerPaysley class
	 *
	 * @return  void
	 */
	public function index()
	{
		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		// Validate minimum quantity requirments.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}
			if ($product['minimum'] > $product_total) {
				$this->response->redirect($this->url->link('checkout/cart'));
			}
		}

		$this->language->load('extension/payment/paysley');

		$this->initApi();

		$payment_widget_url = $this->getPaymentUrl();
		$this->response->redirect($payment_widget_url);
	}

	/**
	 * To load the confirm view
	 *
	 * @return  void
	 */
	public function confirmHtml()
	{
		$this->language->load('extension/payment/paysley');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = $this->url->link('checkout/' . $this->code, '', true);

		return $this->load->view('extension/payment/paysley/confirm', $data);
	}

	/**
	 * Get a payment response then redirect to the payment success page or the payment error page.
	 *
	 * @return  void
	 */
	public function callback()
	{
		$this->load->model('paysley/paysley');
		$this->load->model('checkout/order');

		if (isset($_POST) && count($_POST > 0)) {
			$response_body = $_POST;
		} else {
			$response_body = file_get_contents('php://input');
			$response_body = json_decode($response_body, true);
		}

		$response = json_decode($response_body['response'], true);
		$this->model_paysley_paysley->log('callback - response body : ' . print_r($response_body, true));
		$token = $this->request->get['mp_token'];
		$order_id = $this->request->get['order_id'];

		$transaction_id  = isset($response['transaction_id']) ? $response['transaction_id'] : '';
		$result         = isset($response['result']) ? $response['result'] : '';
		$payment_id      = isset($response['payment_id']) ? $response['payment_id'] : '';
		$amount      	= isset($response['amount']) ? $response['amount'] : '';
		$currency       = isset($response['currency']) ? $response['currency'] : '';
		$generated_token	= $this->generateToken($order_id, $currency);

		$order = $this->model_checkout_order->getOrder($order_id);

		if ($order && 'paysley' === $order['payment_code']) {
			$this->model_paysley_paysley->log('Token : ' . $token);
			$this->model_paysley_paysley->log('OrderId : ' . $order_id);
			$this->model_paysley_paysley->log('Currency : ' . $currency);
			$this->model_paysley_paysley->log('Genereated Token : ' . $generated_token);
			if ($token === $generated_token) {
				$paysley_order_data = [
					'order_id'		=> $order_id,
					'payment_id'	=> $payment_id,
					'amount'		=> $amount,
					'currency'		=> $currency
				];

				if ($result === 'ACK') {
					$this->model_paysley_paysley->log('callback: update order status to processing');
					$order_status_id = $this->config->get('payment_paysley_processing_status_id');
					$paysley_order_data['result'] = 'success';
				} else {
					$this->model_paysley_paysley->log('callback: update order status to failed');
					$order_status_id = $this->config->get('payment_paysley_failed_status_id');
					$paysley_order_data['result'] = 'failed';
				}

				$comment = 'Payment ID: ' . $payment_id . '<br \>';
				$this->model_paysley_paysley->saveOrder($paysley_order_data);
				$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, '', true);
				die('OK');
			} else {
				$this->model_paysley_paysley->log('callback: FRAUD detected, token is not same with the generated token');
			}
		}
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
		$this->load->model('paysley/paysley');
		$this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$this->model_paysley_paysley->log('Paysley Order Info : ' . print_r($order_info, true));
		$currency   = $order_info['currency_code'];
		$amount     = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$transaction_id = 'oc-' . $order_id;
		$secret_key =  $this->generateSecretKey();
		$this->model_paysley_paysley->updatePaysleyData($order_id, 'transaction_id', $transaction_id);
		$this->model_paysley_paysley->updatePaysleyData($order_id, 'secret_key', $secret_key);
		$token      = $this->generateToken($order_id, $currency );

		$body = array(
			'reference'    => $transaction_id,
			'payment_type' => $this->payment_type,
			'currency'     => $currency,
			'amount'       => $amount,
			'cart_items'   => $this->getCartItems(),
			'cancel_url'   => $this->url->link('checkout/checkout', '', true),
			'return_url'   => $this->url->link('checkout/success', '', true),
			'response_url' => $this->url->link('extension/payment/' . $this->code . '/callback&mp_token=' . $token . '&order_id=' . $order_id, '', false),
		);
		$this->model_paysley_paysley->log('Payment Parameters : ' . print_r($body, true));

		$post_link = json_decode(PaysleyApi::generatePosLink($body), 1);
		$this->model_paysley_paysley->log('Post Link : ' . print_r($post_link, true));

		if (isset($post_link['result']) && $post_link['result'] == 'success') {
			return $post_link['long_url'];
		}

		$this->redirectError($post_link['Message']);
	}

	/**
     * redirect to the error message page or the failed message page
     *
     * @param string $error_identifier
     * @return  void
     */
    public function redirectError($error_identifer)
    {
        $this->language->load('extension/payment/paysley');
        $this->session->data['error'] = $error_identifer;
        $this->response->redirect($this->url->link('checkout/checkout', '', true));
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
		$this->load->model('paysley/paysley');

		$cart = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
		$cart_items = array();
		$i = 0;
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
				$unit_price = $item_price + $item_tax;
			}
			
			$cart_items[$i]['sku'] = $sku;
			$cart_items[$i]['unit_price'] = $unit_price;

			$discount_price = (float)$product['price'];
			$special_price = (float)$product['special'];
			$product_price = (float)$this->model_paysley_paysley->getProductPrice($item['product_id']);
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
		$this->load->model('paysley/paysley');
		$payment_paysley_data = $this->model_paysley_paysley->getPaymentPaysleyData($order_id);

		$transaction_id = $payment_paysley_data['transaction_id'];
		$secret_key     = $payment_paysley_data['secret_key'];

		return md5((string)$order_id . $currency . $transaction_id . $secret_key );
	}
}
