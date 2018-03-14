<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ecommerce\Address\Service as AddressService;
use Cradle\Module\Ecommerce\Address\Validator as AddressValidator;

/**
 * Address Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('address-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AddressValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $addressSql = AddressService::get('sql');
    $addressRedis = AddressService::get('redis');
    $addressElastic = AddressService::get('elastic');

    //save address to database
    $results = $addressSql->create($data);
    //link profile
    if(isset($data['profile_id'])) {
        $addressSql->linkProfile($results['address_id'], $data['profile_id']);
    }

    //index address
    $addressElastic->create($results['address_id']);

    //invalidate cache
    $addressRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Address Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('address-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['address_id'])) {
        $id = $data['address_id'];
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
    $addressSql = AddressService::get('sql');
    $addressRedis = AddressService::get('redis');
    $addressElastic = AddressService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $addressRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $addressElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $addressSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $addressRedis->createDetail($id, $results);
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
 * Address Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('address-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the address detail
    $this->trigger('address-detail', $request, $response);

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
    $addressSql = AddressService::get('sql');
    $addressRedis = AddressService::get('redis');
    $addressElastic = AddressService::get('elastic');

    //save to database
    $results = $addressSql->update([
        'address_id' => $data['address_id'],
        'address_active' => 0
    ]);

    //remove from index
    $addressElastic->remove($data['address_id']);

    //invalidate cache
    $addressRedis->removeDetail($data['address_id']);
    $addressRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Address Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('address-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the address detail
    $this->trigger('address-detail', $request, $response);

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
    $addressSql = AddressService::get('sql');
    $addressRedis = AddressService::get('redis');
    $addressElastic = AddressService::get('elastic');

    //save to database
    $results = $addressSql->update([
        'address_id' => $data['address_id'],
        'address_active' => 1
    ]);

    //create index
    $addressElastic->create($data['address_id']);

    //invalidate cache
    $addressRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Address Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('address-search', function ($request, $response) {
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
    $addressSql = AddressService::get('sql');
    $addressRedis = AddressService::get('redis');
    $addressElastic = AddressService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $addressRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $addressElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $addressSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $addressRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Address Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('address-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the address detail
    $this->trigger('address-detail', $request, $response);

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
    $errors = AddressValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $addressSql = AddressService::get('sql');
    $addressRedis = AddressService::get('redis');
    $addressElastic = AddressService::get('elastic');

    //save address to database
    $results = $addressSql->update($data);

    //index address
    $addressElastic->update($response->getResults('address_id'));

    //invalidate cache
    $addressRedis->removeDetail($response->getResults('address_id'));
    $addressRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
