<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Ecommerce\Address;

use Cradle\Module\Ecommerce\Address\Service as AddressService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  address
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getCreateErrors(array $data, array $errors = [])
    { 
        if(!isset($data['address_street']) || empty($data['address_street'])) {
            $errors['address_street'] = 'Address Street is required';
        }
                
        if(!isset($data['address_city']) || empty($data['address_city'])) {
            $errors['address_city'] = 'Address City is required';
        }
                
        if(!isset($data['address_postal']) || empty($data['address_postal'])) {
            $errors['address_postal'] = 'Address Postal is required';
        }
                
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Update Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $data, array $errors = [])
    {
        if(!isset($data['address_id']) || !is_numeric($data['address_id'])) {
            $errors['address_id'] = 'Invalid ID';
        }

        
        if(isset($data['address_street']) && empty($data['address_street'])) {
            $errors['address_street'] = 'Address Street is required';
        }
                
        if(isset($data['address_city']) && empty($data['address_city'])) {
            $errors['address_city'] = 'Address City is required';
        }
                
        if(isset($data['address_postal']) && empty($data['address_postal'])) {
            $errors['address_postal'] = 'Address Postal is required';
        }
                
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $data, array $errors = [])
    {
        //validations
        
        if(isset($data['address_country']) && strlen($data['address_country']) != 2) {
            $errors['address_country'] = 'Invalid Country Code';
        }
                
        return $errors;
    }
}
