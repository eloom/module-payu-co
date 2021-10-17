<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2021 Ã©lOOm (https://eloom.tech)
* @version      1.0.2
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Model\Ui\Pse;

use Eloom\PayUCo\Gateway\Config\Pse\Config as PseConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;

class ConfigProvider implements ConfigProviderInterface {
	
	const CODE = 'eloom_payments_payu_pse';
	
	private $config;
	
	private $session;
	
	protected $escaper;
	
	public function __construct(SessionManagerInterface    $session,
	                            \Magento\Framework\Escaper $escaper,
	                            PseConfig                  $pseConfig) {
		$this->session = $session;
		$this->escaper = $escaper;
		$this->config = $pseConfig;
	}
	
	public function getConfig() {
		$storeId = $this->session->getStoreId();
		
		$payment = [];
		$isActive = $this->config->isActive($storeId);
		if ($isActive) {
			$payment = [
				self::CODE => [
					'isActive' => $isActive,
					'instructions' => $this->getInstructions($storeId),
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