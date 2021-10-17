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

namespace Eloom\PayUCo\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Psr\Log\LoggerInterface;

class PseDataAssignObserver extends AbstractDataAssignObserver {
	
	private $logger;
	
	public function __construct(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	public function execute(Observer $observer) {
		$data = $this->readDataArgument($observer);
		
		$additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
		if (!is_array($additionalData)) {
			return;
		}
		if (!is_object($additionalData)) {
			$additionalData = new DataObject($additionalData ?: []);
		}
		
		$paymentInfo = $this->readPaymentModelArgument($observer);
		$paymentInfo->setAdditionalInformation('pseBank', $additionalData->getPseBank());
		$paymentInfo->setAdditionalInformation('pseUserType', $additionalData->getPseUserType());
	}
}