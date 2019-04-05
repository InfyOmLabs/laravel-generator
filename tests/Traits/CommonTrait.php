<?php namespace Tests\Traits;

use stdClass;

trait CommonTrait
{
    public function mockClassExceptMethods($className, $methods)
    {
        return $this->getMockBuilder($className)
            ->setMethodsExcept($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function prepareFields($fields)
    {
        $objects = [];
        foreach ($fields as $field) {
            $object = new stdClass();
            foreach ($field as $key => $value) {
                $object->$key = $value;
            }
            $objects[] = $object;
        }

        $fields = new stdClass();
        $fields->fields = $objects;

        return $fields;
    }
}