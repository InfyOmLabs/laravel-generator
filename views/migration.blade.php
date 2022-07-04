@php
    echo "<?php".PHP_EOL;
@endphp

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create{{ $config->modelNames->name }}Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{ $config->tableName }}', function (Blueprint $table) {
            {!! $fields !!}
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $config->tableName }}');
    }
}
