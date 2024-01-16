<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2022 elOOm (https://eloom.tech)
* @version      2.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Model;

use Eloom\PayU\Client\ClientFactory;
use Eloom\PayU\Gateway\Config\Config;
use Eloom\PayU\Gateway\PayU\Enumeration\Country;
use Eloom\PayU\Resources\Builder\Payment;
use Eloom\PayUCo\Api\PseBanksInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class PseBanks implements PseBanksInterface {
	
	private $clientFactory;
	
	private $serializer;
	
	private $logger;
	
	private $config;
	
	public function __construct(Config          $config,
	                            LoggerInterface $logger,
	                            Json            $serializer = null,
	                            ClientFactory   $clientFactory) {
		$this->config = $config;
		$this->logger = $logger;
		$this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
		$this->clientFactory = $clientFactory;
	}
	
	public function listBanks() {
		$language = Country::memberByKey('COP')->getLanguage();
		
		$headers = [
			'Content-Type' => 'application/json; charset=UTF-8',
			'Accept' => 'application/json',
			'Accept-Language' => $language];
		
		$body = [
			'language' => $language,
			'command' => 'GET_BANKS_LIST',
			'merchant' => [
				'apiLogin' => $this->config->getLoginApi(),
				'apiKey' => $this->config->getApiKey()
			],
			'bankListInformation' => [
				'paymentMethod' => 'PSE',
				'paymentCountry' => Country::memberByKey('COP')->getCurrency()
			]
		];
		
		$client = $this->clientFactory->create();
		$client->setHeaders($headers);
		$client->post($this->getUrl(), json_encode($body));
		
		$banksResponse = json_decode($client->getBody());
		$banks = [];
		
		if ($banksResponse && $banksResponse->code == 'SUCCESS') {
			foreach ($banksResponse->banks as $bank) {
				$banks[] = [
					'value' => $bank->pseCode, 'label' => $bank->description
				];
			}
		}
		
		return $this->serializer->serialize(['data' => $banks]);
	}
	
	private function getUrl() {
		$environment = $this->config->getEnvironment();
		return Payment::getPaymentsUrl($environment);
	}
}
