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
 * @package  Product
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Ecommerce_Product_EventsTest extends PHPUnit_Framework_TestCase
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
     * product-create
     *
     * @covers Cradle\Module\Ecommerce\Product\Validator::getCreateErrors
     * @covers Cradle\Module\Ecommerce\Product\Validator::getOptionalErrors
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::create
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::create
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::createDetail
     */
    public function testProductCreate()
    {
        $this->request->setStage([
            'product_images' => '[]',
            'product_title' => 'Foobar Title',
            'product_slug' => 'a-Good-slug_1',
            'product_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'product_price' => 100,
            'profile_id' => 1,
            'app_id' => 1,
        ]);

        cradle()->trigger('product-create', $this->request, $this->response);
        $this->assertEquals('[]', $this->response->getResults('product_images'));
        $this->assertEquals('Foobar Title', $this->response->getResults('product_title'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('product_slug'));
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('product_detail'));
        $this->assertEquals(100, $this->response->getResults('product_price'));
        self::$id = $this->response->getResults('product_id');
        $this->assertTrue(is_numeric(self::$id));
    }

    /**
     * product-detail
     *
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     */
    public function testProductDetail()
    {
        $this->request->setStage('product_id', 1);

        cradle()->trigger('product-detail', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('product_id'));
    }

    /**
     * product-remove
     *
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testProductRemove()
    {
        $this->request->setStage('product_id', self::$id);

        cradle()->trigger('product-remove', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('product_id'));
    }

    /**
     * product-restore
     *
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testProductRestore()
    {
        $this->request->setStage('product_id', 581);

        cradle()->trigger('product-restore', $this->request, $this->response);
        $this->assertEquals(self::$id, $this->response->getResults('product_id'));
        $this->assertEquals(1, $this->response->getResults('product_active'));
    }

    /**
     * product-search
     *
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::search
     * @covers Cradle\Module\Ecommerce\Product\Service\ElasticService::search
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getSearch
     */
    public function testProductSearch()
    {
        cradle()->trigger('product-search', $this->request, $this->response);
        $this->assertEquals(1, $this->response->getResults('rows', 0, 'product_id'));
    }

    /**
     * product-update
     *
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::get
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::get
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::getDetail
     * @covers Cradle\Module\Ecommerce\Product\Service\SqlService::update
     * @covers Cradle\Module\Utility\Service\AbstractElasticService::update
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeDetail
     * @covers Cradle\Module\Utility\Service\AbstractRedisService::removeSearch
     */
    public function testProductUpdate()
    {
        $this->request->setStage([
            'product_id' => self::$id,
            'product_images' => '[]',
            'product_title' => 'Foobar Title',
            'product_slug' => 'a-Good-slug_1',
            'product_detail' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
            'product_price' => 100,
            'profile_id' => 1,
            'app_id' => 1,
        ]);

        cradle()->trigger('product-update', $this->request, $this->response);
        $this->assertEquals('[]', $this->response->getResults('product_images'));
        $this->assertEquals('Foobar Title', $this->response->getResults('product_title'));
        $this->assertEquals('a-Good-slug_1', $this->response->getResults('product_slug'));
        $this->assertEquals('One Two Three Four Five Six Seven Eight Nine Ten Eleven', $this->response->getResults('product_detail'));
        $this->assertEquals(100, $this->response->getResults('product_price'));
        $this->assertEquals(self::$id, $this->response->getResults('product_id'));
    }
}
