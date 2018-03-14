<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Ecommerce\Transaction;

use Cradle\Module\Ecommerce\Transaction\Service as TransactionService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  transaction
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
        if(!isset($data['transaction_products']) || empty($data['transaction_products'])) {
            $errors['transaction_products'] = 'Transaction Products is required';
        }
                
        if(!isset($data['transaction_profile']) || empty($data['transaction_profile'])) {
            $errors['transaction_profile'] = 'Transaction Profile is required';
        }
                
        if(!isset($data['transaction_address']) || empty($data['transaction_address'])) {
            $errors['transaction_address'] = 'Transaction Address is required';
        }
                
        if(!isset($data['transaction_total']) || empty($data['transaction_total'])) {
            $errors['transaction_total'] = 'Price is required';
        }
                
        if(!isset($data['transaction_method']) || empty($data['transaction_method'])) {
            $errors['transaction_method'] = 'Transaction Method is required';
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
        if(!isset($data['transaction_id']) || !is_numeric($data['transaction_id'])) {
            $errors['transaction_id'] = 'Invalid ID';
        }

        
        if(isset($data['transaction_products']) && empty($data['transaction_products'])) {
            $errors['transaction_products'] = 'Transaction Products is required';
        }
                
        if(isset($data['transaction_profile']) && empty($data['transaction_profile'])) {
            $errors['transaction_profile'] = 'Transaction Profile is required';
        }
                
        if(isset($data['transaction_address']) && empty($data['transaction_address'])) {
            $errors['transaction_address'] = 'Transaction Address is required';
        }
                
        if(isset($data['transaction_total']) && empty($data['transaction_total'])) {
            $errors['transaction_total'] = 'Price is required';
        }
                
        if(isset($data['transaction_method']) && empty($data['transaction_method'])) {
            $errors['transaction_method'] = 'Transaction Method is required';
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
        
        if (isset($data['transaction_total']) && !is_numeric($data['transaction_total'])) {
            $errors['transaction_total'] = 'Must be a number';
        }
                
        if(isset($data['transaction_total'])
            && is_numeric($data['transaction_total'])
            && $data['transaction_total'] <= 0
        )
        {
            $errors['transaction_total'] = 'Must be a greater than 0';
        }
                
        return $errors;
    }
}
