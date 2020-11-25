@extends('layouts.app')

@section('content')

    <div class="container">

<h1 class="bd-title"><?=$item->getTitle()?></h1>


<?php

if ($canEdit) {
    echo '<p class="text-right">
    <a href="/add/'.$item->id.'" class="btn btn-outline-success">Редактировать</a>';
    if ($item->status == 'trash') {
    	
    } else {
        echo '<a href="/items/'.$item->id.'/?delete" onclick="if (!confirm(\'Удалить?\')) return false;" class="btn text-danger">Удалить</a>';
    }
    echo '<p></p>';
}

if ($item->status == 'trash') {
    echo '<div class="alert alert-danger">Удалено! Вернуться в <a href="/items">список объявлений</a></div>';
} else {
?>

<table class="table">
    <tr>
        <th>#</th>
        <th>Цена</th>
        <th>Цена аренды</th>
        <th>Площадь</th>
        <th>Фото</th>
    </tr>
    <?php
    foreach ($item->offers as $index => $offer) {
        $photo = $offer->getPhoto();
        $preview = $offer->getPhoto(100, 100);
        $link = 'href="#'.$offer->id.'" data-offer';
        echo '
        <tr data-text="'.$offer->text().'" data-photo="'.$photo.'">
            <td>'.++$index.'</td>
            <td><a '.$link.'>'.number_format($offer->price, 0, ' ', ' ').' руб.</a></td>
            <td><a '.$link.'>'.number_format($offer->price_rent, 0, ' ', ' ').' руб./мес</a></td>
            <td><a '.$link.'>'.$offer->so.'м<sup>2</sup></a></td>
            <td><a '.$link.'><img src="'.$preview.'" alt="" /></a></td>
        </tr>';
    }
    ?>
</table>

<hr />


<div class="card-deck">
<?php
$images = $item->getImages();
?>

   <div class="card">

    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
      <ol class="carousel-indicators">
      <?php
      foreach ($images as $key => $img) {
        ?>
        <li data-target="#carouselExampleIndicators" data-slide-to="<?=$key?>"<?=!$key ? ' class="active"' : ''?>></li>
        <?php
      }
      ?>
      </ol>
      <div class="carousel-inner">
      <?php
      foreach ($images as $key => $img) {
        ?>
        <div class="carousel-item <?=!$key ? ' active' : ''?>">
          <img class="d-block w-100" src="<?=$img?>" alt="">
        </div>
        <?php
      }
      ?>
      </div>
      <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>


    <div class="card-body">


      <div class="row">
          <div class="col">

            <h3 class="item-sub">Дата размещения</h3>
            <p class="card-text"><?=date('Y-m-d', strtotime($item['created_at']))?></p>

            <?php if ($item->so) { ?>
            <h3 class="item-sub">Общая площадь</h3>
            <p class="card-text"><?=$item->so?> м<sup>2</sup></p> <?php } ?>

            <?php if ($item->price_about) { ?>
            <h3 class="item-sub">Условия оплаты</h3>
            <p class="card-text"><?=preg_replace('~[\r\n]+~i', '<br />', $item->price_about);?></p> <?php } ?>

            <?php if ($item->content) { ?>
            <h3 class="item-sub">Описание</h3>
            <p class="card-text"><?=preg_replace('~[\r\n]+~i', '<br />', $item->content);?></p> <?php } ?>


          </div>
          <div class="col">
            <h3 class="item-sub">Телефон</h3>
            <p><?=$item->phone?></p>
            <h3 class="item-sub">Контактное лицо</h3>
            <p> <b><?=$item->name ?: 'не указано'?></b></p>


            <?php if ($item->address) { ?>
            <h3 class="item-sub">Адрес</h3>
            <p><?php  echo $item->address ?></p>

            <h3 class="item-sub">Карта</h3>
            <div id="map"></div>
            <script src="https://api-maps.yandex.ru/2.1/?apikey=eed59bbf-f256-4641-bdcc-bd9b733fafab&lang=ru_RU" type="text/javascript">
            </script>

            <script type="text/javascript">
            // Функция ymaps.ready() будет вызвана, когда
            // загрузятся все компоненты API, а также когда будет готово DOM-дерево.
            ymaps.ready(init);
            function init(){
                var myMap = new ymaps.Map("map", {
                    center: [55.76, 37.64],
                    zoom: 15,
                    // behaviors: ["default", "scrollZoom", "rulers"]
                });

                // Поиск точки геокодером
                var myGeocoder = ymaps.geocode('г. Сыктывкар, <?=$item->address?>');
                myGeocoder.then(
                    function (res)
                    {
                        myMap.geoObjects.add(res.geoObjects);
                        myMap.setCenter(res.geoObjects.get(0).geometry.getCoordinates());
                        // как получать свойства из полученных резальтвто
                        // nearest = res.geoObjects.get(0);
                        // var name = nearest.properties.get('name');
                        // nearest.properties.set('iconContent', name);
                        // nearest.options.set('preset', 'twirl#redStretchyIcon');
                    },
                    function (err) { alert('Ошибка'); }
                );
                            }
            </script> <?php } ?>
        </div>
      </div>
    </div>
  </div>
    <?php

?>
</div>
<?php
}
?>


    </div>
@endsection