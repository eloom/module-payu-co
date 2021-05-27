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

namespace Eloom\PayUCo\Cron;

use Eloom\Payment\Api\Data\OrderPaymentInterface;
use Eloom\PayU\Api\Data\OrderPaymentPayUInterface;
use Eloom\PayUCo\Gateway\Config\Baloto\Config as BalotoConfig;
use Eloom\PayUCo\Gateway\Config\Efecty\Config as EfectyConfig;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Payment\Repository;
use Psr\Log\LoggerInterface;

class CancelExpiredVoucher {
	
	private $paymentRepository;
	
	private $searchCriteriaBuilder;
	
	private $logger;
	
	private $balotoConfig;
	
	private $efectyConfig;
	
	private $filterGroupBuilder;
	
	private $filterBuilder;
	
	public function __construct(LoggerInterface $logger,
	                            Repository $paymentRepository,
	                            SearchCriteriaBuilder $searchCriteriaBuilder,
	                            BalotoConfig $balotoConfig,
	                            EfectyConfig $efectyConfig,
	                            FilterBuilder $filterBuilder,
	                            FilterGroupBuilder $filterGroupBuilder) {
		$this->logger = $logger;
		$this->paymentRepository = $paymentRepository;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->balotoConfig = $balotoConfig;
		$this->efectyConfig = $efectyConfig;
		$this->filterBuilder = $filterBuilder;
		$this->filterGroupBuilder = $filterGroupBuilder;
	}
	
	public function execute() {
		if ($this->balotoConfig->isCancelable() || $this->efectyConfig->isCancelable()) {
			$filter = null;
			if ($this->balotoConfig->isCancelable()) {
				$filter = $this->filterBuilder->setField('method')
					->setValue(\Eloom\PayUCo\Model\Ui\Baloto\ConfigProvider::CODE)
					->setConditionType('eq')
					->create();
			}
			
			$filter2 = null;
			if ($this->efectyConfig->isCancelable()) {
				$filter2 = $this->filterBuilder->setField('method')
					->setValue(\Eloom\PayUCo\Model\Ui\Efecty\ConfigProvider::CODE)
					->setConditionType('eq')
					->create();
			}
			$filterGroup = $this->filterGroupBuilder->addFilter($filter)->addFilter($filter2)->create();
			
			// another
			$filter3 = $this->filterBuilder->setField(OrderPaymentPayUInterface::TRANSACTION_STATE)
				->setValue(\Eloom\PayU\Gateway\PayU\Enumeration\PayUTransactionState::PENDING()->key())
				->setConditionType('eq')
				->create();
			
			$filterGroup2 = $this->filterGroupBuilder->addFilter($filter3)->create();
			
			// another
			$filter4 = $this->filterBuilder->setField(OrderPaymentInterface::CANCEL_AT)
				->setValue(date('Y-m-d H:i:s', strtotime('now')))
				->setConditionType('lt')
				->create();
			
			$filterGroup3 = $this->filterGroupBuilder->addFilter($filter4)->create();
			
			$searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$filterGroup, $filterGroup2, $filterGroup3])->create();
			
			/*
			$searchCriteria = $this->searchCriteriaBuilder
				->addFilter('method', \Eloom\PayUCo\Model\Ui\Baloto\ConfigProvider::CODE, 'eq')
				->addFilter(OrderPaymentPayUInterface::TRANSACTION_STATE, \Eloom\PayU\Gateway\PayU\Enumeration\PayUTransactionState::PENDING()->key(), 'eq')
				->addFilter(OrderPaymentInterface::CANCEL_AT, date('Y-m-d H:i:s', strtotime('now')), 'lt')
				->create();
			*/
			
			$paymentList = $this->paymentRepository->getList($searchCriteria)->getItems();
			if (count($paymentList)) {
				$processor = ObjectManager::getInstance()->get(\Eloom\PayU\Model\PaymentManagement\Processor::class);
				
				foreach ($paymentList as $payment) {
					try {
						$this->logger->info(sprintf("%s - Canceling voucher - Order %s", __METHOD__, $payment->getOrder()->getIncrementId()));
						$processor->cancelPayment($payment);
					} catch (\Exception $e) {
						$this->logger->critical(sprintf("%s - Exception: %s", __METHOD__, $e->getMessage()));
						//$this->logger->critical(sprintf("%s - Exception: %s", __METHOD__, $e->getTraceAsString()));
					}
				}
			}
		}
	}
}