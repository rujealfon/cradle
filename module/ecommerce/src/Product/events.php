<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Product\Service as ProductService;
use Cradle\Module\Ecommerce\Product\Validator as ProductValidator;

use Cradle\Module\Utility\File;

/**
 * Product Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('product-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ProductValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['product_images'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['product_images'] = File::base64ToS3($data['product_images'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['product_images'] = File::base64ToUpload($data['product_images'], $upload);
    }

    if(isset($data['product_images'])) {
        $data['product_images'] = json_encode($data['product_images']);
    }

    if(isset($data['product_tags'])) {
        $data['product_tags'] = json_encode($data['product_tags']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $productSql = ProductService::get('sql');
    $productRedis = ProductService::get('redis');
    $productElastic = ProductService::get('elastic');

    //save product to database
    $results = $productSql->create($data);
    //link profile
    if(isset($data['profile_id'])) {
        $productSql->linkProfile($results['product_id'], $data['profile_id']);
    }
    //link app
    if(isset($data['app_id'])) {
        $productSql->linkApp($results['product_id'], $data['app_id']);
    }
    //link comment
    if(isset($data['comment_id'])) {
        $productSql->linkComment($results['product_id'], $data['comment_id']);
    }

    //index product
    $productElastic->create($results['product_id']);

    //invalidate cache
    $productRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Product Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('product-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['product_id'])) {
        $id = $data['product_id'];
    } else if (isset($data['product_slug'])) {
        $id = $data['product_slug'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $productSql = ProductService::get('sql');
    $productRedis = ProductService::get('redis');
    $productElastic = ProductService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $productRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $productElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $productSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $productRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    //if permission is provided
    $permission = $request->getStage('permission');
    if ($permission && $results['profile_id'] != $permission) {
        return $response->setError(true, 'Invalid Permissions');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Product Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('product-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the product detail
    $this->trigger('product-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $productSql = ProductService::get('sql');
    $productRedis = ProductService::get('redis');
    $productElastic = ProductService::get('elastic');

    //save to database
    $results = $productSql->update([
        'product_id' => $data['product_id'],
        'product_active' => 0
    ]);

    //remove from index
    $productElastic->remove($data['product_id']);

    //invalidate cache
    $productRedis->removeDetail($data['product_id']);
    $productRedis->removeDetail($data['product_slug']);
    $productRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Product Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('product-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the product detail
    $this->trigger('product-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $productSql = ProductService::get('sql');
    $productRedis = ProductService::get('redis');
    $productElastic = ProductService::get('elastic');

    //save to database
    $results = $productSql->update([
        'product_id' => $data['product_id'],
        'product_active' => 1
    ]);

    //create index
    $productElastic->create($data['product_id']);

    //invalidate cache
    $productRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Product Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('product-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $productSql = ProductService::get('sql');
    $productRedis = ProductService::get('redis');
    $productElastic = ProductService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $productRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $productElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $productSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $productRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Product Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('product-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the product detail
    $this->trigger('product-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ProductValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['product_images'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['product_images'] = File::base64ToS3($data['product_images'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['product_images'] = File::base64ToUpload($data['product_images'], $upload);
    }

    if(isset($data['product_images'])) {
        $data['product_images'] = json_encode($data['product_images']);
    }

    if(isset($data['product_tags'])) {
        $data['product_tags'] = json_encode($data['product_tags']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $productSql = ProductService::get('sql');
    $productRedis = ProductService::get('redis');
    $productElastic = ProductService::get('elastic');

    //save product to database
    $results = $productSql->update($data);

    //index product
    $productElastic->update($response->getResults('product_id'));

    //invalidate cache
    $productRedis->removeDetail($response->getResults('product_id'));
    $productRedis->removeDetail($data['product_slug']);
    $productRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
