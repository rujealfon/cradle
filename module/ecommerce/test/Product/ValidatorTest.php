<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Product\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Product
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Product_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Ecommerce\Product\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Must have images', $actual['product_images']);
        $this->assertEquals('Title is required', $actual['product_title']);
        $this->assertEquals('Slug is required', $actual['product_slug']);
        $this->assertEquals('Detail is required', $actual['product_detail']);
        $this->assertEquals('Price is required', $actual['product_price']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Product\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['product_id']);
    }
}
