<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
    /**
    * Связанная с моделью таблица.
    * Там должно быть поле id (ai pk) и колонки updated_at и created_at (datetime)
    *
    * @var string
    */
    protected $table = 'dp_pages';

    protected $fillable = ['title', 'content'];
}
