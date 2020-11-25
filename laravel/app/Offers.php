<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    /**
    * Связанная с моделью таблица.
    * Там должно быть поле id (ai pk) и колонки updated_at и created_at (datetime)
    *
    * @var string
    */
    protected $table = 'dp_offers';

    protected $fillable = ['id_object', 'price', 'price_rent', 'price_rent_period', 'so'];

    public function getPhoto($width='', $height='')
    {
        $dir = '/images/offers/'.$this->objects_id.'/';
        $rx = public_path().$dir.$this->id.'.*';
        $file = glob($rx);
        if (!$file) {
            return '/images/nophoto.png';
        }

        if ($width && $height) {
            $pathTo = 'images/preview/'.md5($file[0]).'.jpg';
            $preview = Utils::imagecrop($file[0], $pathTo, $width, $height);
            return '/'.$pathTo;
        }

        return $dir.basename($file[0]);
    
    }

    public function text()
    {
        $add = '';
        if ($this->price) {
        	$add .= ', продажа '.number_format($this->price, 0, ' ', ' ').' руб.';
        }
        if ($this->price_rent) {
        	$add .= ', аренда '.number_format($this->price_rent, 0, ' ', ' ').' руб./мес';
        }
        return 'Площадь '.$this->so.'м2'.$add;
    }
}
