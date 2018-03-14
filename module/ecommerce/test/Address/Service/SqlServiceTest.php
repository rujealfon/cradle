<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Address\Service;

/**
 * SQL service test
 * Address Model Test
 *
 * @vendor   Acme
 * @package  Address
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Address_Service_SqlServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SqlService $object
     */
    protected $object;

    /**
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::__construct
     */
    protected function setUp()
    {
        $this->object = Service::get('sql');
    }

    /**
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::create
     */
    public function testCreate()
    {
        $actual = $this->object->create([
            'address_street' => '123 Sesame Street',
            'address_city' => 'New Year City',
            'address_postal' => '12345',
        ]);

        $id = $this->object->getResource()->getLastInsertedId();

        $this->assertEquals($id, $actual['address_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::get
     */
    public function testGet()
    {
        $actual = $this->object->get(1);

        $this->assertEquals(1, $actual['address_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::search
     */
    public function testSearch()
    {
        $actual = $this->object->search();

        $this->assertArrayHasKey('rows', $actual);
        $this->assertArrayHasKey('total', $actual);
        $this->assertEquals(1, $actual['rows'][0]['address_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::update
     */
    public function testUpdate()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->update([
            'address_id' => $id,
            'address_street' => '123 Sesame Street',
            'address_city' => 'New Year City',
            'address_postal' => '12345',
        ]);

        $this->assertEquals($id, $actual['address_id']);
    }

    /**
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::remove
     */
    public function testRemove()
    {
        $id = $this->object->getResource()->getLastInsertedId();
        $actual = $this->object->remove($id);

        $this->assertTrue(!empty($actual));
        $this->assertEquals($id, $actual['address_id']);
    }

    /**
     * @covers Cradle\Module\Address\Service\SqlService::linkProfile
     */
    public function testLinkProfile()
    {
        $actual = $this->object->linkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['address_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }

    /**
     * @covers Cradle\Module\Address\Service\SqlService::unlinkProfile
     */
    public function testUnlinkProfile()
    {
        $actual = $this->object->unlinkProfile(999, 999);

        $this->assertTrue(!empty($actual));
        $this->assertEquals(999, $actual['address_id']);
        $this->assertEquals(999, $actual['profile_id']);
    }
    
}
