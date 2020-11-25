<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

use App\Task;
use App\User;
use App\Utils;
use App\Objects;

Route::group(['middleware' => ['web']], function () {


    // Главная
    Route::get('/', function () {
        return view('index');
    });

    // Маршруты аутентификации
    Route::get('auth/login', 'Auth\AuthController@getLogin');
    Route::post('auth/login', 'Auth\AuthController@postLogin');
    Route::get('auth/logout', 'Auth\AuthController@logout');

    // Маршруты регистрации
    Route::get('auth/register', 'Auth\AuthController@getRegister');
    Route::post('auth/register', 'Auth\AuthController@postRegister');

    // Подать объявление
    Route::get('/add', 'ObjectsController@addForm');
    Route::post('/add', 'ObjectsController@add');
    Route::get('add/{id}', 'ObjectsController@editForm');
    Route::any('/filesupload', 'ObjectsController@filesupload');
    Route::get('/uploader', 'ObjectsController@uploader');
    Route::get('/itemimages', 'ObjectsController@itemimages');

    // Контакты, сообщения
    Route::get('/contacts', 'ContactsController@index');
    Route::post('/contacts', 'ContactsController@mail');

    // Объявления
    Route::get('/items', 'ObjectItems@items');
    Route::get('items/{id}', 'ObjectItems@item');

    // Страницы
    Route::get('/company', function () {
        return view('company');
    });

    // Новости
    Route::get('/news', 'NewsController@index');
    Route::post('/news', 'NewsController@add');
    Route::get('user/{id}', 'UserController@show');

    Route::get('/cron', function () {
        foreach (glob('./upload/tmp/*') as $k => $v) {
            if (time() - filectime($v) > 3600) {
                Utils::removeDir($v);
            }
        }
    });

});

Route::group(['middleware' => 'web'], function () {
    Route::auth();

    Route::get('/home', 'HomeController@index');
});
