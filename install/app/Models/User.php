<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use SoftDeletes, Notifiable;

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'timezone',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @var array
     */
    public static $labels = [
        'name' => 'Nome',
    ];

    /**
     * Regras padrões para Create e Update
     *
     * @var array
     */
    public static $standard_rules = [
        'name' => ['required', 'min:3'],
        'email' => ['required', 'email'],
        'timezone' => ['required'],
    ];

    /**
     * @return string
     */
    public function getCreatedAtFormatted() {
        return \App\Helpers\DateHelper::getShortDateWithTime($this->created_at);
    }

    /**
     * @return string
     */
    public function getUpdatedAtFormatted() {
        return \App\Helpers\DateHelper::getShortDateWithTime($this->updated_at);
    }
}
