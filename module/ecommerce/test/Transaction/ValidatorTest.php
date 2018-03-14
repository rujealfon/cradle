<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Transaction\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Transaction
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Transaction_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Transaction Products is required', $actual['transaction_products']);
        $this->assertEquals('Transaction Profile is required', $actual['transaction_profile']);
        $this->assertEquals('Transaction Address is required', $actual['transaction_address']);
        $this->assertEquals('Price is required', $actual['transaction_total']);
        $this->assertEquals('Transaction Method is required', $actual['transaction_method']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['transaction_id']);
    }
}
