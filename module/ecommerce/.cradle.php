<?php //-->
include_once __DIR__ . '/src/Address/events.php';
include_once __DIR__ . '/src/Product/events.php';
include_once __DIR__ . '/src/Transaction/events.php';

use Cradle\Module\Ecommerce\Address\Service as AddressService;
use Cradle\Module\Ecommerce\Product\Service as ProductService;
use Cradle\Module\Ecommerce\Transaction\Service as TransactionService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('address', AddressService::class);
ServiceFactory::register('product', ProductService::class);
ServiceFactory::register('transaction', TransactionService::class);
