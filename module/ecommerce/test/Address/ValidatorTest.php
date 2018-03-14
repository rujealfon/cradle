<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Address\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Address
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Address_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Ecommerce\Address\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Address Street is required', $actual['address_street']);
        $this->assertEquals('Address City is required', $actual['address_city']);
        $this->assertEquals('Address Postal is required', $actual['address_postal']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Address\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['address_id']);
    }
}
