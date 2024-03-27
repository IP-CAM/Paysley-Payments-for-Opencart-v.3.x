<?php
namespace Opencart\Catalog\Controller\Extension\Paysley\Api;
/**
* 2020 Paysley
*
* NOTICE OF Paysley
*
* This source file is subject to the General Public License) (GPL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/gpl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author    Paysley <info@paysley.com>
*  @copyright 2020 Paysley
*  @license   https://www.gnu.org/licenses/gpl-3.0.html  General Public License (GPL 3.0)
*  International Registered Trademark & Property of Paysley
*/

/**
 * Handles POS Link, Refunds and other API requests.
 *
 * @since 1.0.0
 */
class PaysleyApi
{
	/**
	 * API Access Key
	 *
	 * @var string
	 */
	public static $access_key;

	/**
	 * Is use test server or not
	 *
	 * @var bool
	 */
	public static $is_test_mode = false;

	/**
	 * API live url
	 *
	 * @var string
	 */
	public static $api_live_url = 'https://live.paysley.io/v2';

	/**
	 * API test url
	 *
	 * @var string
	 */
	public static $api_test_url = 'https://test.paysley.io/v2';

	/**
	 * Get API url
	 *
	 * @return string
	 */
	public static function getApiUrl()
	{
		if (self::$is_test_mode) {
			return self::$api_test_url;
		}
		return self::$api_live_url;
	}

	/**
	 * Send request to the API
	 *
	 * @param string $url Url.
	 * @param array  $body Body.
	 * @param string $method Method.
	 *
	 * @return array
	 */
	public static function sendRequest($url, $body = '', $method = 'GET')
	{
		$headers = array("Authorization:Bearer " . self::$access_key);
		if ('POST' === $method || 'PUT' === $method) {
			array_push($headers, "content-type: application/json");
		}
		$data = json_encode($body);

		$curl_data = array(
			CURLOPT_CUSTOMREQUEST => strtoupper($method),
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 70,
			CURLOPT_HTTPHEADER => $headers
		);

		if ('POST' === $method || 'PUT' === $method) {
			$curl_data[CURLOPT_POSTFIELDS] = $data;
		}
		$curl = curl_init();
		curl_setopt_array($curl, $curl_data);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
			echo "cURL Error #:" . $err;
		}

		return json_decode($response, true);
	}

	/**
	 * Get pos link url with the API.
	 *
	 * @param array $body Body.
	 *
	 * @return array
	 */
	public static function generatePosLink($body)
	{
		$url = self::getApiUrl() . '/payment-requests';
		return self::sendRequest($url, $body, 'POST');
	}

	/**
	 * Do refund with the API.
	 *
	 * @param string $payment_id Payment ID.
	 * @param array  $body Body.
	 *
	 * @return array
	 */
	public static function doRefund($payment_id, $body)
	{
		$url = self::getApiUrl() . '/refunds/' . $payment_id;
		return self::sendRequest($url, $body, 'POST');
	}


	/**
	 * Get payment detail with the API.
	 *
	 * @param string $payment_id Payment ID.
	 *
	 * @return array
	 */
	public static function getPayment($payment_id)
	{
		$url = self::getApiUrl() . '/payments/' . $payment_id;
		return self::sendRequest($url);
	}

	/**
	 * Function to create the product on the paysley
	 * Create new Product.
	 */
	public static function createProduct($body)
	{
		$url = self::getApiUrl() . '/products-services';
		return self::sendRequest($url, $body, 'POST');
	}

	/**
	 * Create new Product.
	 */
	public static function updateProduct($body)
	{
		$url = self::getApiUrl() . '/products-services';
		return self::sendRequest($url, $body, 'PUT');
	}
	/**
	 *  Lists of Product.
	 */
	public static function getProducts($productName = null)
	{
		$url = self::getApiUrl() . '/products-services';
		if (!empty($productName)) {
			$url .= "?keywords=".urlencode($productName);
		}
		return self::sendRequest($url);
	}

	/**
	 * Create new category.
	 */
	public static function createCategory($body)
	{
		$url = self::getApiUrl() . '/products-services/category';
		return self::sendRequest($url, $body, 'POST');
	}



	/**
	 * Get list of categories.
	 *
	 * @return array
	 */
	public static function categoryList($categoryName = null)
	{
		$url = self::getApiUrl() . '/products-services/category';
		if (!empty($categoryName)) {
			$url .= "?keywords=".urlencode($categoryName);
		}
		return self::sendRequest($url);
	}

	
	/**
	 * Customer List.
	 */
	public static function customerList($searchKeyword = null)
	{
		$url = self::getApiUrl() . '/customers';
		if ($searchKeyword)
			$url .= "?keywords=".urlencode($searchKeyword);
		return self::sendRequest($url);
	}

	/**
	 * Update Customer.
	 */
	public static function updateCustomer($body)
	{
		$url = self::getApiUrl() . '/customers';
		return self::sendRequest($url, $body, 'PUT');
	}

	
	/**
	 * Create new Customer.
	 */
	public static function createCustomer($body)
	{
		$url = self::getApiUrl() . '/customers';
		return self::sendRequest($url, $body, 'POST');
	}

	
	/**
	 * Get payment details
	 */
	public static function getPaymentDetails($transactionId = null)
	{
		$url = self::getApiUrl() . '/payment-requests/'.$transactionId;
		return self::sendRequest($url);
	}
}
