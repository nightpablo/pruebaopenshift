<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Middleware\ApiAuthMiddleware;


 Route::get('/', function () {
     return 'hola mundo con holis holis laravel';
 });
 Route::get('/welcome', function () {
     return view('welcome');
 });
 Route::get('/pruebas/{nombre?}',function($nombre=null){
     return "Hecho desde otra ruta".$nombre;
 });
 Route::get('/animales','PruebasController@index');
 Route::get('/test-orm','PruebasController@testOrm');

// Rutas del controlador de usuario
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');
Route::put('/api/user/update','UserController@update');
Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}','UserController@getImage');
Route::get('/api/user/detail/{id}','UserController@detail');

// Rutas del controlador de categorias
Route::resource('/api/category','CategoryController');

// Rutas del controlador de posts
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload','PostController@upload');
Route::get('/api/post/image/{filename}','PostController@getImage');
Route::get('/api/post/category/{id}','PostController@getPostByCategory');
Route::get('/api/post/user/{id}','PostController@getPostByUser');
