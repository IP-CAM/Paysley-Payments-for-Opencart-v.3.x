## additional instruction to show logo at checkout payment method
------------------------------------------------------------------

1. go to file {your shop root folder}/catalog/view/theme/default/template/checkout/payment_method.twig

change line 14:
	{{ payment_method.title }}

to this code:
	{% if payment_method.code == 'paysley' %}
        {{ payment_method.logo }}
    {% else %}
        {{ payment_method.title }}
    {% endif %}

## additional instruction to refund and update status at backend order history.
--------------------------------------------------------------------------------------------

1. go to file {your shop root folder}/catalog/controller/api/order.php

at line 795:
	if ($order_info) {
		$this->model_checkout_order->addOrderHistory($order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override']);

		$json['success'] = $this->language->get('text_success');
	} else {
		$json['error'] = $this->language->get('error_not_found');
	}

change to this code:
	if ($order_info) {
		if ($order_info['payment_code'] == 'paysley')
		{
			$this->load->language('extension/payment/paysley');
			$this->load->model('paysley/paysley');

			$order_status_id = $this->request->post['order_status_id'];
			$result = $this->model_paysley_paysley->refundPayment($order_info, $order_status_id);

			if ($result['status'])
			{
				$this->model_checkout_order->addOrderHistory($order_id, $result['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override']);
				$json['success'] = $this->language->get($result['successMessage']);
			} else {
				$json['error'] = $this->language->get($result['errorMessage']);
			}
		}
		else
		{
			$this->model_checkout_order->addOrderHistory($order_id, $this->request->post['order_status_id'], $this->request->post['comment'], $this->request->post['notify'], $this->request->post['override']);

			$json['success'] = $this->language->get('text_success');
		}
	} else {
		$json['error'] = $this->language->get('error_not_found');
	}