<?php

namespace InfyOm\Generator\Common;

use Exception;

abstract class BaseRepository extends \Prettus\Repository\Eloquent\BaseRepository
{
    public function findWithoutFail($id, $columns = ['*'])
    {
        try {
            return $this->find($id, $columns);
        } catch (Exception $e) {
            return;
        }
    }
}
