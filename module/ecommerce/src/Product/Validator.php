<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Ecommerce\Product;

use Cradle\Module\Ecommerce\Product\Service as ProductService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  product
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
        if(!isset($data['product_images']) || empty($data['product_images'])) {
            $errors['product_images'] = 'Must have images';
        }
                
        if(!isset($data['product_title']) || empty($data['product_title'])) {
            $errors['product_title'] = 'Title is required';
        }
                
        if(!isset($data['product_slug']) || empty($data['product_slug'])) {
            $errors['product_slug'] = 'Slug is required';
        }
                
        if(!isset($data['product_detail']) || empty($data['product_detail'])) {
            $errors['product_detail'] = 'Detail is required';
        }
                
        if(!isset($data['product_price']) || empty($data['product_price'])) {
            $errors['product_price'] = 'Price is required';
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
        if(!isset($data['product_id']) || !is_numeric($data['product_id'])) {
            $errors['product_id'] = 'Invalid ID';
        }

        
        if(isset($data['product_images']) && empty($data['product_images'])) {
            $errors['product_images'] = 'Must have images';
        }
                
        if(isset($data['product_title']) && empty($data['product_title'])) {
            $errors['product_title'] = 'Title is required';
        }
                
        if(isset($data['product_slug']) && empty($data['product_slug'])) {
            $errors['product_slug'] = 'Slug is required';
        }
                
        if(isset($data['product_detail']) && empty($data['product_detail'])) {
            $errors['product_detail'] = 'Detail is required';
        }
                
        if(isset($data['product_price']) && empty($data['product_price'])) {
            $errors['product_price'] = 'Price is required';
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
        
        if(isset($data['product_title']) && strlen($data['product_title']) <= 10) {
            $errors['product_title'] = 'Title should be longer than 10 characters';
        }
                
        if(isset($data['product_title']) && strlen($data['product_title']) >= 255) {
            $errors['product_title'] = 'Title should be less than 255 characters';
        }
                
        if (isset($data['product_slug']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['product_slug'])) {
            $errors['product_slug'] = 'Slug must only have letters, numbers, dashes';
        }
                
        if(isset($data['product_slug'])) {
            $search = Service::get('sql')
                ->getResource()
                ->search('product')
                ->filterByProductSlug($data['product_slug']);

            if(isset($data['product_id'])) {
                $search->addFilter('product_id != %s', $data['product_id']);
            }

            if($search->getTotal()) {
                $errors['product_slug'] = 'Slug must be unique';
            }
        }
                
        if(isset($data['product_detail']) && str_word_count($data['product_detail']) <= 10) {
            $errors['product_detail'] = 'Detail should have more than 10 words';
        }
                
        if (isset($data['product_price']) && !is_numeric($data['product_price'])) {
            $errors['product_price'] = 'Must be a number';
        }
                
        if(isset($data['product_price'])
            && is_numeric($data['product_price'])
            && $data['product_price'] <= 0
        )
        {
            $errors['product_price'] = 'Must be a greater than 0';
        }
                
        if (isset($data['product_original']) && !is_numeric($data['product_original'])) {
            $errors['product_original'] = 'Must be a number';
        }
                
        if(isset($data['product_original'])
            && is_numeric($data['product_original'])
            && $data['product_original'] <= 0
        )
        {
            $errors['product_original'] = 'Must be a greater than 0';
        }
                
        return $errors;
    }
}
