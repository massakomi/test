@extends('layouts.app')

@section('content')
    <div class="container">

    <div class="row" style="margin-top:2rem;">
        <div class="col">
            <h1 class="bd-title" style="margin-top:0px;">Коммерческая недвижимость</h1>
        </div>
        <div class="col">
        <a href="?id_nedv=1" class="btn btn-primary">Продать</a>
        &nbsp;
        <a href="?id_nedv=3" class="btn btn-primary">Сдать</a>
        </div>
    </div>


    <div class="card-columns">
    <?php
    foreach ($items as $item) {
        $images = $item->getImages();
        $price = $priceRent = '';
        foreach ($item->offers as $offer) {
            if ($offer->price && (!$price || $offer->price < $price)) {
            	$price = $offer->price;
            }
            if ($offer->price_rent && (!$price || $offer->price < $price)) {
            	$priceRent = $offer->price_rent;
            }
        }
        if ($price) {
        	$price = ' - '.number_format($price, 0, ' ', ' ').' руб.';
        }
        if ($priceRent) {
        	$priceRent = ' - '.number_format($price, 0, ' ', ' ').' руб.';
        }
    ?>
       <div class="card">
        <a href="/items/<?=$item->id?>"><img src="<?=@$images[0]?>" class="card-img-top" alt=""></a>
        <div class="card-body">
          <h5 class="card-title">
            <?php
            if ($price) {
                echo ' <div>Продажа '.$price.'</div>';
            }
            if ($priceRent) {
                echo ' <div>Аренда '.$priceRent.'</div>';
            }
            ?>
          </h5>
          <p style="font-weight:bold;"><?=$item->type?></p>
          <?php
          foreach ($item->offers as $offer) {
            $preview = $offer->getPhoto();
            echo ' <span data-photo="'.$preview.'" data-text="'.$offer->text().'"><a href="#" data-offer class="btn btn-outline-secondary btn-sm">'.$offer->so.'м<sup>2</sup></a></span>';
          }
          ?>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col">
              <div class="card-text" style="line-height:18px;"><small class="text-danger"><?=$item['name']?></small>
              <small class="text-muted"><?=$item['created_at']?></small></div>
            </div>
            <div class="col text-right"><a class="btn btn-primary" href="/items/<?=$item->id?>">Подробнее</a></div>
          </div>
          
        </div>
      </div>
        <?php
    }
    ?>
    </div>


    </div>
@endsection
