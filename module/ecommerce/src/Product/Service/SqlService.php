<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Ecommerce\Product\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Product SQL Service
 *
 * @vendor   Acme
 * @package  product
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'product';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        return $this->resource
            ->model($data)
            ->setProductCreated(date('Y-m-d H:i:s'))
            ->setProductUpdated(date('Y-m-d H:i:s'))
            ->save('product')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('product');
        
        $search->innerJoinUsing('product_profile', 'product_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->innerJoinUsing('product_app', 'product_id');
        $search->innerJoinUsing('app', 'app_id');
        
        if (is_numeric($id)) {
            $search->filterByProductId($id);
        } else if (isset($data['product_slug'])) {
            $search->filterByProductSlug($id);
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['product_images']) {
            $results['product_images'] = json_decode($results['product_images'], true);
        } else {
            $results['product_images'] = [];
        }

        if($results['product_tags']) {
            $results['product_tags'] = json_decode($results['product_tags'], true);
        } else {
            $results['product_tags'] = [];
        }

        return $results;
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setProductId($id)
            ->remove('product');
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;
        
        $keywords = null;
        
        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        
        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }
        

        
        if (!isset($filter['product_active'])) {
            $filter['product_active'] = 1;
        }
        

        $search = $this->resource
            ->search('product')
            ->setStart($start)
            ->setRange($range);

        
        //join profile
        $search->innerJoinUsing('product_profile', 'product_id');
        $search->innerJoinUsing('profile', 'profile_id');
        //join app
        $search->innerJoinUsing('product_app', 'product_id');
        $search->innerJoinUsing('app', 'app_id');
        

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        
        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(product_brand) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }
        

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {
            
            if($results['product_images']) {
                $rows[$i]['product_images'] = json_decode($results['product_images'], true);
            } else {
                $rows[$i]['product_images'] = [];
            }
            
            if($results['product_tags']) {
                $rows[$i]['product_tags'] = json_decode($results['product_tags'], true);
            } else {
                $rows[$i]['product_tags'] = [];
            }
            
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        return $this->resource
            ->model($data)
            ->setProductUpdated(date('Y-m-d H:i:s'))
            ->save('product')
            ->get();
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $productSlug
     *
     * @return bool
     */
    public function exists($productSlug)
    {
        $search = $this->resource
            ->search('product')
            ->filterByProductSlug($productSlug);

        return !!$search->getRow();
    }
    

    /**
     * Links profile
     *
     * @param *int $productPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($productPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->setProfileId($profilePrimary)
            ->insert('product_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $productPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($productPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->setProfileId($profilePrimary)
            ->remove('product_profile');
    }
    

    /**
     * Links app
     *
     * @param *int $productPrimary
     * @param *int $appPrimary
     */
    public function linkApp($productPrimary, $appPrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->setAppId($appPrimary)
            ->insert('product_app');
    }

    /**
     * Unlinks app
     *
     * @param *int $productPrimary
     * @param *int $appPrimary
     */
    public function unlinkApp($productPrimary, $appPrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->setAppId($appPrimary)
            ->remove('product_app');
    }
    

    /**
     * Links comment
     *
     * @param *int $productPrimary
     * @param *int $commentPrimary
     */
    public function linkComment($productPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->setCommentId($commentPrimary)
            ->insert('product_comment');
    }

    /**
     * Unlinks comment
     *
     * @param *int $productPrimary
     * @param *int $commentPrimary
     */
    public function unlinkComment($productPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->setCommentId($commentPrimary)
            ->remove('product_comment');
    }

    /**
     * Unlinks All comment
     *
     * @param *int $productPrimary
     * @param *int $commentPrimary
     */
    public function unlinkAllComment($productPrimary)
    {
        return $this->resource
            ->model()
            ->setProductId($productPrimary)
            ->remove('product_comment');
    }
    
}
