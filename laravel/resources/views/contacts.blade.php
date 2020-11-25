@extends('layouts.app')

@section('title', 'Контакты')

@section('content')
    <div class="container">
    <div class="contacts">
        <h1 class="bd-title">Контакты</h1>

        <div class="row">
            <div class="col-md-5 text-center">
                <img src="/images/logo.png" style="width:150px;" alt="" />
            </div>
            <div class="col-md-4">
                <a href="#" data-toggle="modal" data-target="#modal-form" data-backdrop="static" data-whatever="@callback" class="btns"><img src="/images/btn-callback.png" alt="" /></a>
                <a href="#" data-toggle="modal" data-target="#modal-form" data-backdrop="static" data-whatever="@message" class="btns"><img src="/images/btn-msg.png" alt="" /></a>
            </div>
            <div class="col-md-3">
                <div class="grey-text">+7 (8212) 24 99 33</div>
                <div class="grey-text">8 9222 722247</div>
                <div class="grey-text">Email: domkomi@mail.ru</div>
            </div>
        </div>
<br /><br />




        <div class="row">
            <div class="col-md-5">

            <div class="grey-rounded contacts-block">
                <div class="row">
                    <div class="col t">
                    ТЕЛЕФОН
                    </div>
                    <div class="col text-right">
                        <div>+7 (8212) 24 99 33</div>
                        <a href="#" data-toggle="modal" data-backdrop="static" data-target="#modal-form" data-whatever="@callback" class="abs">ОБРАТНЫЙ ЗВОНОК</a>
                    </div>
                </div>
            </div>
            <div class="grey-rounded contacts-block">
                <div class="row">
                    <div class="col t">
                    ЭЛ.ПОЧТА
                    </div>
                    <div class="col text-right">
                        <div><a href="mailto:domkomi@mail.ru">domkomi@mail.ru</a></div>
                        <a href="#" data-toggle="modal" data-backdrop="static" data-target="#modal-form" data-whatever="@message" class="abs">НАПИСАТЬ СООБЩЕНИЕ</a>
                    </div>
                </div>
            </div>
            <div class="grey-rounded contacts-block">
                <div class="row">
                    <div class="col t">
                    НАШ АДРЕС
                    </div>
                    <div class="col text-right">
                        <div>&nbsp;</div>
                        <div class="abs">167000, Республика Коми<br />г. Сыктывкар<br />
                        ул. Сысольское шоссе, 1/3</div>
                    </div>
                </div>
            </div>
            <div class="grey-rounded contacts-block">
                <div class="row">
                    <div class="col t">
                    РЕЖИМ РАБОТЫ
                    </div>
                    <div class="col text-right">
                        <div>&nbsp;</div>
                        <div class="abs">ПН-ПТ с 10 до 17</div>
                    </div>
                </div>
            </div>

            </div>
            <div class="col-md-7">
                <img src="/images/map.png" alt="" />
                <br /><br />
                <img src="/images/office.jpg" alt="" />
            </div>
        </div>

    </div>

    <div class="bottom-block grey-rounded">
        <b>АДМИНИСТРАТОРЫ ОБЪЕКТОВ</b>
        <div class="row">
            <div class="col">
            Клавдия Николаевна<br />
            Тел. 723-003
            </div>
            <div class="col">
            Мария Леонидовна<br />
            Тел. 722-247
            </div>
            <div class="col">
            Анжелика Ивановна<br />
            Тел. 8 904 270 7118
            </div>
        </div>

    </div>
    </div>


@endsection


@section('js')

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

<script type="text/javascript">
$('#modal-form').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var recipient = button.data('whatever')
    var modal = $(this)
    if (recipient == '@callback') {
    	modal.find('.modal-title').text('Обратный звонок')
        modal.find('.txt').hide()
    }
    if (recipient == '@message') {
    	modal.find('.modal-title').text('Написать сообщение')
        modal.find('.txt').show()
    }
    modal.find('[name="phone"]').focus()
})
$('#contacts-form').submit(function(e) {
    e.preventDefault()
    $.post('', $(this).serialize(), $.proxy(function(data) {
        $(this).html('<div class="alert alert-success">Спасибо! <br />Ваше сообщение отправлено</div>')
    }, this));
})
</script>
@endsection