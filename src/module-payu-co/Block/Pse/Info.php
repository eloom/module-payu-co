<?php
/**
* 
* PayU Colombia para Magento 2
* 
* @category     elOOm
* @package      Modulo PayUCo
* @copyright    Copyright (c) 2022 Ã©lOOm (https://eloom.tech)
* @version      2.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Eloom\PayUCo\Block\Pse;

use Magento\Sales\Model\Order;

class Info extends \Eloom\PayU\Block\Info {
	
	public function getPaymentLink() {
		return $this->getInfo()->getAdditionalInformation('paymentLink');
	}
	
	public function getPdfLink() {
		return $this->getInfo()->getAdditionalInformation('pdfLink');
	}
	
	public function getBankUrl() {
		$order = $this->getInfo()->getOrder();
		if (Order::STATE_NEW == $order->getState()) {
			return $this->getInfo()->getAdditionalInformation('bankUrl');
		}
		
		return null;
	}
}
