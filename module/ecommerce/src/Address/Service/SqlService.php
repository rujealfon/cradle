<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Ecommerce\Address\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Address SQL Service
 *
 * @vendor   Acme
 * @package  address
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'address';

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
            ->setAddressCreated(date('Y-m-d H:i:s'))
            ->setAddressUpdated(date('Y-m-d H:i:s'))
            ->save('address')
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
        $search = $this->resource->search('address');
        
        $search->innerJoinUsing('address_profile', 'address_id');
        $search->innerJoinUsing('profile', 'profile_id');
        
        $search->filterByAddressId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
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
            ->setAddressId($id)
            ->remove('address');
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
        

        
        if (!isset($filter['address_active'])) {
            $filter['address_active'] = 1;
        }
        

        $search = $this->resource
            ->search('address')
            ->setStart($start)
            ->setRange($range);

        
        //join profile
        $search->innerJoinUsing('address_profile', 'address_id');
        $search->innerJoinUsing('profile', 'profile_id');
        

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
                $where[] = 'LOWER(address_label) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_contact) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_phone) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_street) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_neighborhood) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_city) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_state) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_region) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_country) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(address_postal) LIKE %s';
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
            ->setAddressUpdated(date('Y-m-d H:i:s'))
            ->save('address')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $addressPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($addressPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setAddressId($addressPrimary)
            ->setProfileId($profilePrimary)
            ->insert('address_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $addressPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($addressPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setAddressId($addressPrimary)
            ->setProfileId($profilePrimary)
            ->remove('address_profile');
    }
    
}
