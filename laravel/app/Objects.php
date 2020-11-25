<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Objects extends Model
{
    /**
    * Связанная с моделью таблица.
    * Там должно быть поле id (ai pk) и колонки updated_at и created_at (datetime)
    *
    * @var string
    */
    protected $table = 'dp_objects';

    protected $fillable = ['id_nedv', 'type', 'so', 'address', 'price', 'price_rent', 'price_about', 'phone', 'content', 'name', 'user_id'];

    /**
    * Получить
    */
    public function offers()
    {
        return $this->hasMany('App\Offers');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    function getNedvName()
    {
        return ($this->id_nedv == 1 ? 'Продаю ': 'Сдаю ');
    }

    public function getTitle()
    {
        return $this->getNedvName().' '.$this->type;
    }

    public function getImages()
    {
        if (!$this->id) {
            return [];
        }
        $images = glob('images/items/'.$this->id.'/*');
        foreach ($images as $k => &$v) {
        	$v = '/'.$v;
        }
        return $images;
    }
}
