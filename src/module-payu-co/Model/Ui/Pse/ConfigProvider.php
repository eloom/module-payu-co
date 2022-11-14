<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2022 elOOm (https://eloom.tech)
* @version      1.0.5
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Model\Ui\Pse;

use Eloom\PayUCo\Gateway\Config\Pse\Config as PseConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface {

	const CODE = 'eloom_payments_payu_pse';

	protected $assetRepo;

	private $config;

	protected $escaper;

	protected $storeManager;

	private static $allowedCurrencies = ['COP', 'USD'];

	public function __construct(Repository              $assetRepo,
	                            Escaper                 $escaper,
	                            PseConfig               $pseConfig,
	                            StoreManagerInterface $storeManager) {
		$this->assetRepo = $assetRepo;
		$this->escaper = $escaper;
		$this->config = $pseConfig;
		$this->storeManager = $storeManager;
	}

	public function getConfig() {
		$store = $this->storeManager->getStore();
		$payment = [];
		$storeId = $store->getStoreId();
		$isActive = $this->config->isActive($storeId);
		if ($isActive) {
			$currency = $store->getCurrentCurrencyCode();
			if (!in_array($currency, self::$allowedCurrencies)) {
				return ['payment' => [
					self::CODE => [
						'message' =>  sprintf("Currency %s not supported.", $currency)
					]
				]];
			}

			$payment = [
				self::CODE => [
					'isActive' => $isActive,
					'instructions' => $this->getInstructions($storeId),
					'url' => [
						'logo' => $this->assetRepo->getUrl('Eloom_PayUCo::images/pse.svg')
					],
					'userType' => [
						['v' => 'N', 't' => __('Natural')],
						['v' => 'J', 't' => __('Legal')]
					]
				]
			];
		}

		return [
			'payment' => $payment
		];
	}

	protected function getInstructions($storeId): string {
		return $this->escaper->escapeHtml($this->config->getInstructions($storeId));
	}
}
