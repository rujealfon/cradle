<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Product\Service;

/**
 * SQL service test
 * Product Model Test
 *
 * @vendor   Acme
 * @package  Product
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Product_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'product_images' => '[]',
            'product_title' => 'Foobar Title',
            'product_slug' => 'a-Good-slug_1',
            'product_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'product_price' => 100,
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['product_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['product_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['product_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'product_id' => $id,
            'product_images' => '[]',
            'product_title' => 'Foobar Title',
            'product_slug' => 'a-Good-slug_1',
            'product_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'product_price' => 100,
        ]);

        $this->assertEquals($id, $actual['product_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::exists
     */
    public function testExists()
    { 
        $actual = $this->object->exists('a-Good-slug_1');
        // it returns a boolean so we're expecting it to be true because
        // the slug provided is saved in the database
        $this->assertTrue($actual);
    }
    

    /**
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['product_id']);
    }

    /**
     * @covers Cradle\Module\Product\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Product\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    

    /**
     * @covers Cradle\Module\Product\Service\SqlService::linkApp
     */
    public function testLinkApp()
    {
        $actual = $this->object->linkApp(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
        $this->assertEquals(999, $actual['app_id']);
    }

    /**
     * @covers Cradle\Module\Product\Service\SqlService::unlinkApp
     */
    public function testUnlinkApp()
    {
        $actual = $this->object->unlinkApp(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
        $this->assertEquals(999, $actual['app_id']);
    }
    

    /**
     * @covers Cradle\Module\Product\Service\SqlService::linkComment
     */
    public function testLinkComment()
    {
        $actual = $this->object->linkComment(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
        $this->assertEquals(999, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\Product\Service\SqlService::unlinkComment
     */
    public function testUnlinkComment()
    {
        $actual = $this->object->unlinkComment(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
        $this->assertEquals(999, $actual['comment_id']);
    }

    /**
     * @covers Cradle\Module\Product\Service\SqlService::unlinkComment
     */
    public function testUnlinkAllComment()
    {
        $actual = $this->object->unlinkAllComment(999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['product_id']);
    }
    
}
