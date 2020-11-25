<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/5cf6fef93f.js"></script>

    <title>@yield('title', 'Дом Плюс Недвижимость')</title>

    <link rel="stylesheet" href="/css/main.css?<?=filemtime('css/main.css')?>" type="text/css" media="all" />
  </head>
  <body>
    <div id="master-wrapper"></div>

    <div class="container text-right">
        <div class="phone-top">+7 (8212) 24 99 33</div>
        <div class="logo-top">
            <a class="nedv-for" target="_blank" href="http://komu.info/">ДЛЯ ЖИЗНИ</a>
            <span class="nedv-for">ДЛЯ БИЗНЕСА</span>
        </div>
    </div>

    <div class="container wide-bg">
        <div class="container">
            <div class="menu-block">
                <a href="/contacts">Контакты</a>
                <a href="/add" class="add-item">Подать <br />объявление</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="menu">
            <ul class="row">
                 <li class="col"><a href="http://komu.info/?id_nedv=1" target="_blank">Продажа</a></li>
                 <li class="col"><a href="http://komu.info/?id_nedv=4" target="_blank">Аренда</a></li>
                 <li class="col-4"><a href="/items">Коммерческий бизнес</a></li>
                 <li class="col"><a href="/news">Новости</a></li>
                 <li class="col"><a href="/company">Компания</a></li>
            </ul>
        </div>
    </div>

    <div class="page-content">
        @yield('content')
    </div>


    <footer>
    <div class="container pt-5 pb-5">
        <div class="row">
            <div class="col">

        <!-- Yandex.Metrika informer -->
        <p><a href="https://metrika.yandex.ru/stat/?id=51945710&amp;from=informer"
        target="_blank" rel="nofollow"><img src="https://informer.yandex.ru/informer/51945710/3_1_FFFFFFFF_EFEFEFFF_0_pageviews"
        style="width:88px; height:31px; border:0;" alt="Яндекс.Метрика" title="Яндекс.Метрика: данные за сегодня (просмотры, визиты и уникальные посетители)" class="ym-advanced-informer" data-cid="51945710" data-lang="ru" /></a></p>
       
        <!-- /Yandex.Metrika informer -->

            <!-- Yandex.Metrika counter -->
            <script type="text/javascript" >
               (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
               m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
               (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

               ym(51945710, "init", {
                    id:51945710,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true
               });
            </script>
            <noscript><div><img src="https://mc.yandex.ru/watch/51945710" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
            <!-- /Yandex.Metrika counter -->
             © 1999 год <div class="text-nowrap">ООО Дом Плюс</div>
             Управляющая компания<br />
             Агентство недвижимости<br />
             <a href="mailto:domkomi@mail.ru">domkomi@mail.ru</a> телефон <span class="text-nowrap">8 (8212) 249933</span>
            </div>
            <div class="col">
                <ul>
                 <li><a href="/">Главная</a></li>
                 <li><a href="/company">О компании</a></li>
                 <li><a href="/news">Новости</a></li>
                 <li><a href="/contacts">Контакты</a></li>
                </ul>
            </div>
            <div class="col">
                <div style="font-family:Arial; text-transform: uppercase; margin-bottom:5px;">Продажа / аренда</div>
                <ul>
                 <li><a href="/items?type=1">торговые</a></li>
                 <li><a href="/items?type=2">офисные</a></li>
                 <li><a href="/items?type=3">производственные</a></li>
                 <li><a href="/items?type=4">складские</a></li>
                 <li><a href="/items?type=5">спортивные</a></li>
                 <li><a href="/items?type=6">для услуг</a></li>
                 <li><a href="/items?type=7">комплексы</a></li>
                </ul>
            </div>
            <div class="col">
                <a href="http://komu.info/?id_nedv=1" target="_blank">Продажа жилья</a><br />
                <a href="http://komu.info/?id_nedv=4" target="_blank">Аренда жилья</a>
            </div>
            <div class="col">
                <p><a href="/add" class="btn btn-danger btn-sm">Подать объявление</a></p>
                <p><a href="#" class="btn btn-outline-light btn-sm" data-toggle="modal" data-backdrop="static" data-target="#modal-form" data-whatever="@message">Написать сообщение</a></p>
                Адрес: Коми, г.Сыктывкар, Сысольское шоссе 1/3

                <?php
                if (Auth::id()) {
                    ?>
                    <p class="mt-2"><a href="/auth/logout" class="btn btn-sm btn-warning">Выйти</a> (<?=Auth::user()->name?>)</p>
                    <?php
                } else {
                    ?>
                    <p class="mt-2"><a href="/auth/login" class="btn btn-sm btn-success">Войти</a></p>
                    <?php
                }
                ?>
                
            </div>
        </div>
        
    </div>
    </footer>

<div class="modal fade" tabindex="-1" role="dialog" id="modal-form">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Обратный звонок</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">


         <form id="contacts-form">
           {{ csrf_field() }}
          <div class="form-group">
            <label>Ваше имя</label>
            <input type="text" name="name" class="form-control" value="" placeholder="">
          </div>
          <div class="form-group">
            <label>Телефон</label>
            <input type="text" name="phone" required class="form-control" value="" placeholder="">
          </div>
          <div class="form-group txt">
            <textarea name="message" placeholder="Ваше сообщение" rows="3" class="form-control"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Отправить</button>
        </form>

      </div>
    </div>
  </div>
</div>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript">
<?php if (isset($_COOKIE['dev'])) { ?>
    window.onerror = function (text, file, line) {
    	alert(text+' '+file+':'+line)
    }
    <?php } ?>
</script>

@yield('js')

  </body>
</html>