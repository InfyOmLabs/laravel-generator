<?php
/**
 * @copyright Copyright (c) 2018. Wubbleyou Ltd
 */

namespace InfyOm\Generator\Utils;


class GeneratorTable
{
    /** @var string */
    public $primaryKey;

    /** @var GeneratorForeignKey[] */
    public $foreignKeys;
}