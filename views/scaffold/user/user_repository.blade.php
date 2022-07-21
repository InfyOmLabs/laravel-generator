@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ config('laravel_generator.namespace.repository') }};

use {{ config('laravel_generator.namespace.repository') }}\BaseRepository;
use {{ config('laravel_generator.namespace.model') }}\User;

/**
 * Class UserRepository
 * @package {{ config('laravel_generator.namespace.repository') }}
*/

class UserRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'password'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return User::class;
    }
}
