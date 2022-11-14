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

namespace Eloom\PayUCo\Api;

/**
 * Interface for PSE Banks.
 * @api
 * @since 100.0.2
 */
interface PseBanksInterface {
	
	/**
	 * List all banks.
	 *
	 * @return string
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function listBanks();
}