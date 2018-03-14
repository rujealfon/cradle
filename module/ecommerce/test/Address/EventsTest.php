<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Event test
 *
 * @vendor   Acme
 * @package  Address
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Address_EventsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Request $response
     */
    protected $response;

    /**
     * @var int $id
     */
    protected static $id;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();

        $this->request->load();
        $this->response->load();
    }

    /**
     * address-create
     *
     * @covers Cradle\Module\Ecommerce\Address\Validator::getCreateErrors
     * @covers Cradle\Module\Ecommerce\Address\Validator::getOptionalErrors
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testAddressCreate()
    {
        $this->request->setStage([
            'address_street' => '123 Sesame Street',
            'address_city' => 'New Year City',
            'address_postal' => '12345',
            'profile_id' => 1,
        ]);

        cradle()->trigger('address-create', $this->request, $this->response);
        $this->assertEquals('123 Sesame Street', $this->response->getResults('address_street'));
        $this->assertEquals('New Year City', $this->response->getResults('address_city'));
        $this->assertEquals('12345', $this->response->getResults('address_postal'));
        self::$id = $this->response->getResults('address_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * address-detail
     *
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testAddressDetail()
    {
        $this->request->setStage('address_id', 1);

        cradle()->trigger('address-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('address_id'));
    }

    /**
     * address-remove
     *
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAddressRemove()
    {
        $this->request->setStage('address_id', self::$id);

        cradle()->trigger('address-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('address_id'));
    }

    /**
     * address-restore
     *
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAddressRestore()
    {
        $this->request->setStage('address_id', 581);

        cradle()->trigger('address-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('address_id'));
        $this->assertEquals(1, $this->response->getResults('address_active'));
    }

    /**
     * address-search
     *
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::search
     * @covers Cradle\Module\Ecommerce\Address\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testAddressSearch()
    {
        cradle()->trigger('address-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'address_id'));
    }

    /**
     * address-update
     *
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ecommerce\Address\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testAddressUpdate()
    {
        $this->request->setStage([
            'address_id' => self::$id,
            'address_street' => '123 Sesame Street',
            'address_city' => 'New Year City',
            'address_postal' => '12345',
            'profile_id' => 1,
        ]);

        cradle()->trigger('address-update', $this->request, $this->response);
        $this->assertEquals('123 Sesame Street', $this->response->getResults('address_street'));
        $this->assertEquals('New Year City', $this->response->getResults('address_city'));
        $this->assertEquals('12345', $this->response->getResults('address_postal'));
        $this->assertEquals(self::$id, $this->response->getResults('address_id'));
    }
}
