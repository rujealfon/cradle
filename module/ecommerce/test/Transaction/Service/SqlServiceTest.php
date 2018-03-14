<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Transaction\Service;

/**
 * SQL service test
 * Transaction Model Test
 *
 * @vendor   Acme
 * @package  Transaction
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Transaction_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'transaction_products' => '[]',
            'transaction_profile' => '[]',
            'transaction_address' => '[]',
            'transaction_total' => 200.55,
            'transaction_method' => 'paypal',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'transaction_id' => $id,
            'transaction_products' => '[]',
            'transaction_profile' => '[]',
            'transaction_address' => '[]',
            'transaction_total' => 200.55,
            'transaction_method' => 'paypal',
        ]);

        $this->assertEquals($id, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Transaction\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['transaction_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['transaction_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Transaction\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['transaction_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    
}
