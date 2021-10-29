<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2021 Ã©lOOm (https://eloom.tech)
* @version      1.0.3
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Model\Ui\Baloto;

use Eloom\PayUCo\Gateway\Config\Baloto\Config as BalotoConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\Escaper;

class ConfigProvider implements ConfigProviderInterface {

	const CODE = 'eloom_payments_payu_baloto';

	protected $assetRepo;

	private $config;

	private $session;

	protected $escaper;

	public function __construct(Repository              $assetRepo,
	                            SessionManagerInterface $session,
	                            Escaper $escaper,
	                            BalotoConfig $balotoConfig) {
		$this->assetRepo = $assetRepo;
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
					'instructions' => $this->getInstructions($storeId),
					'url' => [
						'logo' => $this->assetRepo->getUrl('Eloom_PayUCo::images/baloto.svg')
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