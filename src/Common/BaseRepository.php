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

    public function create(array $attributes)
    {
        // Have to skip presenter to get a model not some data
        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);
        $model = parent::create($attributes);
        $this->skipPresenter($temporarySkipPresenter);

        $model = $this->updateRelations($model, $attributes);
        $model->save();

        return $this->parserResult($model);
    }

    public function update(array $attributes, $id)
    {
        // Have to skip presenter to get a model not some data
        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);
        $model = parent::update($attributes, $id);
        $this->skipPresenter($temporarySkipPresenter);

        $model = $this->updateRelations($model, $attributes);
        $model->save();

        return $this->parserResult($model);
    }

    /**
     * Search the given ID in the array.
     * @param $id Integer
     * @param $array Array
     * @param $keyName string
     *
     * @return bool
     */
    private function findIdExists($id, $array, $keyName)
    {
        $exists = false;
        for ($k = 0; $k < count($array); $k++){
            if(isset($array[$k])){
                if(isset($array[$k][$keyName]) && $array[$k][$keyName] == $id){
                    $exists = true;
                    $k = count($array);
                }
            }
        }

        return $exists;
    }

    /**
     * Find the position of the ID in the given array.
     * @param $id Integer
     * @param $array Array_
     * @param $keyName String_
     *
     * @return int|null
     */
    private function findIndex($id, $array, $keyName)
    {
        $position = null;

        for ($k = 0; $k < count($array); $k++){
            if(isset($array[$k])){
                if(isset($array[$k][$keyName]) && $array[$k][$keyName] == $id){
                    $position = $k;
                    $k = count($array);
                }
            }
        }

        return $position;
    }

    public function updateRelations($model, $attributes)
    {
        foreach ($attributes as $key => $val) {
            if (isset($model) &&
                method_exists($model, $key) &&
                is_a(@$model->$key(), 'Illuminate\Database\Eloquent\Relations\Relation')
            ) {
                $methodClass = get_class($model->$key($key));
                switch ($methodClass) {
                    case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                        $new_values = array_get($attributes, $key, []);
                        $check_values = array_get($attributes, $key, []);
                        if (array_search('', $new_values) !== false) {
                            unset($new_values[array_search('', $new_values)]);
                        }
                        // If there is any value passed
                        if(count($check_values)){
                            $first_element = array_shift($check_values);
                            // Verify if is array, and if there is, adjust the index to be the id of the relation
                            if(is_array($first_element)){
                                $otherKeyTxt = $model->$key()->getOtherKey();
                                $otherKeyArray = explode('.', $otherKeyTxt);
                                $otherKey = array_pop($otherKeyArray);
                                $final_array = [];
                                foreach ($new_values as $idx => $item){
                                    $index = $idx;
                                    if(isset($item[$otherKey])){
                                        $index = $item[$otherKey];
                                        unset($item[$otherKey]);
                                    }
                                    $final_array[$index] = $item;

                                }
                                $new_values = $final_array;
                            }else{
                                $new_values = array_values($new_values);
                            }
                        }
                        
                        $model->$key()->sync($new_values);
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\BelongsTo':
                        $model_key = $model->$key()->getForeignKey();
                        $new_value = array_get($attributes, $key, null);
                        $new_value = $new_value == '' ? null : $new_value;
                        $model->$model_key = $new_value;
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOne':
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasOneOrMany':
                        break;
                    case 'Illuminate\Database\Eloquent\Relations\HasMany':

                        $new_values = array_get($attributes, $key, []);
                        sort($new_values);
                        // The name of the class
                        $related = get_class($model->$key()->getRelated());

                        if (array_search('', $new_values) !== false) {
                            unset($new_values[array_search('', $new_values)]);
                        }
                        list($temp, $model_key) = explode('.', $model->$key($key)->getForeignKey());

                        $model_instance = new $related();
                        // Get the name of the primary key
                        $keyName = $model_instance->getKeyName();
                        // Find if the id exists in the itens received
                        foreach ($model->$key as $rel) {
                            if (!$this->findIdExists($rel->$keyName, $new_values, $keyName)) {
                                $rel->delete();
                            }else{
                                $position = $this->findIndex($rel->$keyName, $new_values, $keyName);
                                if(!is_null($position)){
                                    $related = get_class($model->$key()->getRelated());
                                    $related::where($keyName,$rel->$keyName)->update($new_values[$position]);
                                    unset($new_values[$position]);
                                }
                            }

                        }
                        // Insert the new ones
                        if (count($new_values) > 0) {
                            foreach ($new_values as $val) {
                                $val[$model_key] = $model->id;
                                $rel = $related::firstOrNew($val);
                                $rel->save();
                            }
                        }
                        break;
                }
            }
        }

        return $model;
    }
}
