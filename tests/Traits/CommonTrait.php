<?php

namespace Tests\Traits;

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
        foreach ($fields as $field) { // $field should contain array e.g [property1 => value, property2 => value, ....]
            $objects[] = (object) $field; // convert array to object
        }

        $fields = new stdClass();
        $fields->fields = $objects;

        return $fields;
    }
}
