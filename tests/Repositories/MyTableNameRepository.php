<?php

namespace Tests\Repositories;

use Tests\Models\MyTable;
/**
 * Class MyTableNameRepository
 * @package Tests\Repositories
 */
class MyTableNameRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'field1_id',
        'field2_id',
        'field3_id'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return MyTable::class;
    }
}