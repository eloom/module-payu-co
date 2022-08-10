<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2022 elOOm (https://eloom.tech)
* @version      1.0.4
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Gateway\Request\Payment;

use Eloom\PayU\Gateway\PayU\Enumeration\Country;
use Eloom\PayU\Gateway\PayU\Enumeration\PaymentMethod;
use Eloom\PayU\Gateway\Request\Payment\AuthorizeDataBuilder;
use Eloom\PayU\Helper\MappedOrderAttributeDefinition;
use Eloom\PayUCo\Gateway\Config\Baloto\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class PseDataBuilder implements BuilderInterface {

	const COOKIE = 'cookie';

	const USER_AGENT = 'userAgent';

	const PAYMENT_METHOD = 'paymentMethod';

	const EXPIRATION_DATE = 'expirationDate';

	private $config;

	private $cookieManager;

	private $httpHeader;

	protected $urlBuilder;

	public function __construct(Config                 $config,
	                            CookieManagerInterface $cookieManager,
	                            Header                 $httpHeader,
	                            UrlInterface           $urlBuilder) {
		$this->config = $config;
		$this->cookieManager = $cookieManager;
		$this->httpHeader = $httpHeader;
		$this->urlBuilder = $urlBuilder;
	}

	public function build(array $buildSubject) {
		$paymentDataObject = SubjectReader::readPayment($buildSubject);
		$payment = $paymentDataObject->getPayment();
		$order = $payment->getOrder();

		$attributeDefinition = ObjectManager::getInstance()->get(MappedOrderAttributeDefinition::class);
		$taxvat = $attributeDefinition->getTaxvat($order);
		$taxvat = preg_replace('/\D/', '', $taxvat);

		$extraParameters = [
			'RESPONSE_URL' => $this->urlBuilder->getUrl('sales/order/history'),
			'FINANCIAL_INSTITUTION_CODE' => $payment->getAdditionalInformation('pseBank'),
			'USER_TYPE' => $payment->getAdditionalInformation('pseUserType'),
			'PSE_REFERENCE3' => $taxvat,
		];

		$country = Country::memberByKey($order->getOrderCurrencyCode());
		if ($country->isColombia()) {
			if (null != $order->getBillingAddress()->getDnitype()) {
				$extraParameters['PSE_REFERENCE2'] = $order->getBillingAddress()->getDnitype();
			}
		}

		return [AuthorizeDataBuilder::TRANSACTION => [
			self::PAYMENT_METHOD => PaymentMethod::memberByKey('pse')->getCode(),
			self::COOKIE => $this->cookieManager->getCookie('PHPSESSID'),
			self::USER_AGENT => $this->httpHeader->getHttpUserAgent(),
			'extraParameters' => $extraParameters
		]];
	}
}