<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayU Colombia
* @copyright    Copyright (c) 2022 elOOm (https://eloom.tech)
* @version      1.0.5
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Gateway\Request\Payment;

use Eloom\PayU\Gateway\PayU\Enumeration\PaymentMethod;
use Eloom\PayU\Gateway\Request\Payment\AuthorizeDataBuilder;
use Eloom\PayUCo\Gateway\Config\Efecty\Config;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class EfectyDataBuilder implements BuilderInterface {
	
	const COOKIE = 'cookie';
	
	const USER_AGENT = 'userAgent';
	
	const PAYMENT_METHOD = 'paymentMethod';
	
	const EXPIRATION_DATE = 'expirationDate';
	
	private $config;
	
	private $cookieManager;
	
	private $httpHeader;
	
	public function __construct(Config $config,
	                            CookieManagerInterface $cookieManager,
	                            Header $httpHeader) {
		$this->config = $config;
		$this->cookieManager = $cookieManager;
		$this->httpHeader = $httpHeader;
	}
	
	public function build(array $buildSubject) {
		$paymentDataObject = SubjectReader::readPayment($buildSubject);
		$payment = $paymentDataObject->getPayment();
		$storeId = $payment->getOrder()->getStoreId();
		
		$expiration = new \DateTime('now +' . $this->config->getExpiration($storeId) . ' day');
		
		return [AuthorizeDataBuilder::TRANSACTION => [
			self::PAYMENT_METHOD => PaymentMethod::memberByKey('efecty')->getCode(),
			self::COOKIE => $this->cookieManager->getCookie('PHPSESSID'),
			self::USER_AGENT => $this->httpHeader->getHttpUserAgent(),
			self::EXPIRATION_DATE => $expiration->format('Y-m-d\TH:i:s'),
			'extraParameters' => [
				'INSTALLMENTS_NUMBER' => 1
			]
		]];
	}
}