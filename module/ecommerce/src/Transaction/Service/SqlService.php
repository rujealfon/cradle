<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Ecommerce\Transaction\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Transaction SQL Service
 *
 * @vendor   Acme
 * @package  transaction
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'transaction';

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
            ->setTransactionCreated(date('Y-m-d H:i:s'))
            ->setTransactionUpdated(date('Y-m-d H:i:s'))
            ->save('transaction')
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
        $search = $this->resource->search('transaction');
        
        $search->innerJoinUsing('transaction_profile', 'transaction_id');
        $search->innerJoinUsing('profile', 'profile_id');
        
        $search->filterByTransactionId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['transaction_products']) {
            $results['transaction_products'] = json_decode($results['transaction_products'], true);
        } else {
            $results['transaction_products'] = [];
        }

        if($results['transaction_profile']) {
            $results['transaction_profile'] = json_decode($results['transaction_profile'], true);
        } else {
            $results['transaction_profile'] = [];
        }

        if($results['transaction_address']) {
            $results['transaction_address'] = json_decode($results['transaction_address'], true);
        } else {
            $results['transaction_address'] = [];
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
            ->setTransactionId($id)
            ->remove('transaction');
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
        

        
        if (!isset($filter['transaction_active'])) {
            $filter['transaction_active'] = 1;
        }
        

        $search = $this->resource
            ->search('transaction')
            ->setStart($start)
            ->setRange($range);

        
        //join profile
        $search->innerJoinUsing('transaction_profile', 'transaction_id');
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
                $where[] = 'LOWER(transaction_products) LIKE %s';
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
            
            if($results['transaction_products']) {
                $rows[$i]['transaction_products'] = json_decode($results['transaction_products'], true);
            } else {
                $rows[$i]['transaction_products'] = [];
            }
            
            if($results['transaction_profile']) {
                $rows[$i]['transaction_profile'] = json_decode($results['transaction_profile'], true);
            } else {
                $rows[$i]['transaction_profile'] = [];
            }
            
            if($results['transaction_address']) {
                $rows[$i]['transaction_address'] = json_decode($results['transaction_address'], true);
            } else {
                $rows[$i]['transaction_address'] = [];
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
            ->setTransactionUpdated(date('Y-m-d H:i:s'))
            ->save('transaction')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $transactionPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($transactionPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setTransactionId($transactionPrimary)
            ->setProfileId($profilePrimary)
            ->insert('transaction_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $transactionPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($transactionPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setTransactionId($transactionPrimary)
            ->setProfileId($profilePrimary)
            ->remove('transaction_profile');
    }
    
}
