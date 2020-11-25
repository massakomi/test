@extends('layouts.app')

@section('content')
    <div class="container img-btn normal">
      <div class="row">
        <div class="col-sm">
         <!-- <a href="http://komu.info/" target="_blank"></a> -->
         <img src="/images/btn-1.jpg?1" alt="" />
         <div class="plash">
             <a href="http://komu.info/?id_nedv=1">
                Продажа
             </a>
             <a href="http://komu.info/?id_nedv=4">
                Аренда
             </a>
         </div>
        </div>
        <div class="col-sm">
         <img src="/images/btn-2.jpg?1" alt="" />

         <div class="plash">
             <a href="/items?id_nedv=1">
                Продажа
             </a>
             <a href="/items?id_nedv=4">
                Аренда
             </a>
         </div>

        </div>
      </div>
    </div>
@endsection
