<?php
/**
* 
* PayU ColÃ´mbia para Magento 2
* 
* @category     Ã©lOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2021 Ã©lOOm (https://www.eloom.dev)
* @version      1.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Model\Ui\Baloto;

use Eloom\PayUCo\Gateway\Config\Baloto\Config as BalotoConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;

class ConfigProvider implements ConfigProviderInterface {

	const CODE = 'eloom_payments_payu_baloto';

	private $config;

	private $session;

	protected $escaper;

	public function __construct(SessionManagerInterface $session,
	                            \Magento\Framework\Escaper $escaper,
	                            BalotoConfig $balotoConfig) {
		$this->session = $session;
		$this->escaper = $escaper;
		$this->config = $balotoConfig;
	}

	public function getConfig() {
		$storeId = $this->session->getStoreId();

		$payment = [];
		$isActive = $this->config->isActive($storeId);
		if ($isActive) {
			$payment = [
				self::CODE => [
					'isActive' => $isActive,
					'instructions' => $this->getInstructions($storeId)
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