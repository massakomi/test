@extends('layouts.app')

@section('title')
<?=$title?>
@endsection


@section('content')
    <div class="container">

    <?php
    
    /*if ($username) {
        echo '<div class="alert alert-warning">Вы авторизованы как <b>'.$username.'</b>. <a href="/auth/logout">Выйти</a></div>';
    }*/

    if ($item) {
        // echo '<pre>'; print_r($item); echo '</pre>';
    }


    ?>


    <h1 class="bd-title"><?=$title?></h1>


<form action="{{ url('add')}}" method="POST" id="add-form">
  {{ csrf_field() }}
  <div class="form-row">
    <div class="form-group col-md-4">
      <select name="id_nedv" class="form-control">
          <option value="1"<?=$item->id_nedv == 1 ? ' selected' : ''?>>Продать</option>
          <option value="3"<?=$item->id_nedv == 3 ? ' selected' : ''?>>Сдать</option>
      </select>
    </div>
    <div class="form-group col-md-8">
      <input type="text" name="type" value="<?=$item->type?>" data-required class="form-control" placeholder="Что? Магазин, офис, торговое помещение и т.п.">
    </div>
  </div>
  <div class="form-row">
      <div class="form-group col-md-8">
        <label>Адрес</label>
        <input type="text" name="address" value="<?=$item->address?>" class="form-control">
      </div>
      <div class="form-group col-md-4">
        <label>Общая площадь, м<sup>2</sup></label>
        <input type="number" name="so" value="<?=$item->so?>" step="0.1" class="form-control">
      </div>
  </div>
  <div class="form-row">
      <div class="form-group col-md-12">
        <label>Описание</label>
        <textarea name="content" class="form-control" placeholder="Опишите особенности вашего объекта недвижимости"><?=$item->content?></textarea>
      </div>
      <div class="form-group col-md-12">
        <label>Условия оплаты, аренды</label>
        <textarea name="price_about" class="form-control" class="form-control" placeholder=""><?=$item->price_about?></textarea>
      </div>
  </div>

<!--   <div class="form-row">
      <div class="form-group col-md-2">
        <label>Цена, руб</label>
        <input type="number" name="price" class="form-control">
      </div>
      <div class="form-group col-md-2">
        <label>Цена аренды, руб/мес</label>
        <input type="number" name="price_rent" class="form-control">
      </div>
      <div class="form-group col-md-4">
        <label>Условия оплаты, аренды</label>
        <input type="text" name="price_about" class="form-control">
      </div>
  </div>
   -->

    <hr />

  <div class="form-row">
      <div class="form-group col-md-12">
        <b>Предложения по объекту</b>
      </div>
      <div class="alert alert-danger" id="offers-errors" style="display:none;"></div>
  </div>


  <div id="offers-body">
  <?php
  foreach ($item->offers as $offer) {
    ?>
      <div class="form-row">
          <div class="form-group col-md-2">
            <label>Цена, руб</label>
            <input type="text" name="offer-edit[price][<?=$offer->id?>]" value="<?=$offer->price?>" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Цена аренды, руб.</label>
            <input type="text" name="offer-edit[price_rent][<?=$offer->id?>]" value="<?=$offer->price_rent?>" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Период аренды</label>
            <select name="offer-edit[price_rent_period][<?=$offer->id?>]" class="form-control">
                <option value="month"<?=$offer->price_rent_period=='month'?' selected':''?>>в месяц</option>
                <option value="kvm"<?=$offer->price_rent_period=='month'?' selected':''?>>за кв.м. в месяц</option>
            </select>
          </div>
          <div class="form-group col-md-2">
            <label>Площадь</label>
            <input type="text" name="offer-edit[so][<?=$offer->id?>]" value="<?=$offer->so?>" class="form-control">
          </div>
          <div class="form-group col-md-4">
            <label>Фотография (планировка)</label>
              <div class="custom-file">
                <input type="file" class="custom-file-input simple-file" name="offer-edit[file][<?=$offer->id?>]">
                <label class="custom-file-label">Заменить фото</label>
             </div>
              <div class="photo-block"><?php
              $src = $offer->getPhoto(100, 100);
              if ($src) {
                ?>
                    <img src="<?=$src?>" class="mt-2" alt="" />

                <?php
              } else {
                  echo '&nbsp;';
              }
              ?></div>
          </div>
      </div>
    <?php
  }
  ?>
      <div class="form-row offer-copy">
          <div class="form-group col-md-2">
            <label>Цена, руб</label>
            <input type="text" name="offer[price][]" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Цена аренды, руб.</label>
            <input type="text" name="offer[price_rent][]" class="form-control">
          </div>
          <div class="form-group col-md-2">
            <label>Период аренды</label>
            <select name="offer-edit[price_rent_period][]" class="form-control">
                <option value="month">в месяц</option>
                <option value="kvm">за кв.м. в месяц</option>
            </select>
          </div>
          <div class="form-group col-md-2">
            <label>Площадь</label>
            <input type="text" name="offer[so][]" class="form-control">
          </div>
          <div class="form-group col-md-4">
            <label>Фотография (планировка)</label>
              <div class="custom-file">
                <input type="file" class="custom-file-input simple-file" name="offer[file][]">
                <label class="custom-file-label">Выбрать</label>
             </div>
             <div class="photo-block"></div>
          </div>
      </div>

  </div>
  <div class="form-group text-right">
    <input class="btn btn-primary" type="button" onclick="app.offerCopy(this.parentNode); return false;" value="Добавить предложение">
  </div>
   <hr />

  <div class="form-row">
      <div class="form-group col-md-12">
          <label>Общие фотографии объекта</label>

            <div id="photo-errors" class="alert alert-danger" style="display:none;"></div>

            <div id="photoUploader">
                @include('add_uploader')
            </div>

            <div id="uploader"></div>

            <div id="item-images">
                <div id="item-images-inner"></div>
            </div>

            <script type="text/javascript">
            function reloadImages()
            {
                $.get('/itemimages', function(data) {
                    if (data != '') {
                        $('#item-images').show()
                        $('#item-images-inner').html(data)
                    } else {
                        $('#item-images').hide()
                    }
                });
            }
            </script>

         <?php
         $images = $item->getImages();
         if ($images) {
             echo '<div class="mt-2">';
             foreach ($images as $img) {
                echo ' <img src="'.$img.'" class="img-thumbnail" style="width:100px;" alt="" />';
             }
             echo '</div>';
         }

         ?>
        <div id="img-preview"></div>
      </div>
  </div>

  <div class="form-row">
      <div class="form-group col-md-6">
        <label>Контактное лицо</label>
        <input type="text" name="name" value="<?=$item->name?>" class="form-control">
      </div>
      <div class="form-group col-md-6">
        <label>Телефон</label>
        <input type="text" name="phone" value="<?=$item->phone?>" data-required class="form-control">
      </div>
  </div>


    <button class="btn btn-primary" type="submit" id="agmeTitle">
      <span class="spinner-border spinner-border-sm" style="display:none;" role="status" aria-hidden="true"></span>
      <span class="text"><?=$btnText?></span>
    </button>
    <input type="hidden" name="id" value="<?=$item->id?>">
</form>

<br /><br />

    </div>
@endsection





@section('js')

<script type="text/javascript">

app = {}

app.init = function() {

    $('input[name^="offer[price]"], input[name^="offer[price_rent]"]').keyup(function(e) {
        price_format(this, 1, e);
    })

    $('textarea').keyup(app.formconfig)
    $('textarea').each(app.formconfig)

    $('#add-form').on('change', '.simple-file', app.browseSimple);

    $('#add-form [name="id_nedv"]').on('change', function() {
        $('#offers-body .form-group:first-child').toggle(this.value != 3)
    });

    $('#add-form').submit(function (e) {
        e.preventDefault()
        app.loading()
        app.save(this)
        return false;
    })
}

app.formconfig = function() {
    if (this.value.length > 300) {
    	this.style.height = '200px'
    }
    if (this.value.length > 600) {
    	this.style.height = '300px'
    }
}

app.loading = function(hide) {
    if (typeof(hide) == 'undefined') {
        $('#add-form').find('[type="submit"]').attr('disabled', true)
        $('#add-form').find('[type="submit"] .text').html('Подождите...')
        $('#add-form').find('.spinner-border').show()
    } else {
        $('#add-form').find('[type="submit"]').attr('disabled', false)
        $('#add-form').find('[type="submit"] .text').html('Добавить объявление')
        $('#add-form').find('.spinner-border').hide()
    }
}

app.offerCopy = function (btn)
{
    var el = $('.offer-copy').clone().removeClass('offer-copy').get()
    $(el).find('label:not(.custom-file-label)').hide()
    $(el).find('.photo-block').html('')
    $(el).appendTo('#offers-body');
}


// Основные фотки
app.browse = function() {

}

// Фотки предложений
app.browseSimple = function() {
    let files = this.files
    let browser = $(this).parent().next()

    if (!files.length) {
        return;
    }
    $(browser).html('')
    for (var i=0, file; file = files[i]; i++) {
        if (file.type.match('image.*')) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(browser).append('<img src="'+e.target.result+'" style="width:100px;" alt="" />');
            }
            reader.readAsDataURL(file);
        }
    }
}

app.save = function(form) {

    var formData = new FormData(form);
    $.ajax({
        url : '/add',
        type : 'POST',
        data : formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success : $.proxy(function(data) {
            app.loading(false)

            if (typeof(data['card-id']) != 'undefined') {
                var url = '/items/'+data['card-id'];
            	$('#add-form').html('<div class="alert alert-success"><?=$addedTxt?> Спасибо! <br /><b><a href="'+url+'">Перейти в карточку объявления</a></b></div>')
                setTimeout(function() {
                    location = url;
                }, 2000);
                return ;
            }

            $('.is-invalid').removeClass('is-invalid')
            $('.invalid-tooltip').remove()
            $('#offers-errors').hide()
            if (data['offers-errors']) {
                $('#offers-errors').show().html('')
                console.log(data['offers-errors'])
                if (typeof data['offers-errors'] == 'string') {
                	$('#offers-errors').append(data['offers-errors'])
                } else {
                    for (var index in data['offers-errors']) {
                        var msgs = data['offers-errors'][index];
                        for (var field in msgs) {
                            var msg = msgs[field];
                            //$(this).find('[name="'+field+'"], [name^="'+field+'"]:first').addClass('is-invalid').after('<div class="invalid-tooltip">'+msg+'</div>')
                            $('#offers-errors').append('#'+index+') '+msg+'<br />')
                            //console.log(index+' / '+field+' * '+msg)
                        }
                    }
                }

            } else if (data['photo-errors']) {
                $('#photo-errors').show().html(data['photo-errors'])

            } else {
                for (var field in data) {
                    var msg = data[field];
                    $(form).find('[name="'+field+'"], [name^="'+field+'["]:first').addClass('is-invalid').after('<div class="invalid-tooltip">'+msg+'</div>')
                }
            }

            setTimeout(function() {
                $('.invalid-tooltip').remove()
            }, 5000);

        }, this)
    });
}

app.init()


</script>



@endsection
