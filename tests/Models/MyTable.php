<?php
/**
 * Company: InfyOm Technologies, Copyright 2019, All Rights Reserved.
 * Author: Vishal Ribdiya
 * Email: vishal.ribdiya@infyom.com
 * Date: 29-07-2019
 * Time: 11:32 AM.
 */

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class User.
 */
class MyTable extends Model
{
    protected $table = 'my_tables';

    protected $fillable = [
        'field1_id',
        'field2_id',
        'field3_id',
    ];
}
