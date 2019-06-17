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

Route::get('/', function () {
    return redirect('home');
});

Auth::routes();

Route::get('/home', 'StockController@index_action')->name('home')->middleware("auth");
Route::get('/stock/quote', 'StockController@quote_action')->name('stock_quote')->middleware("auth");

// social login
Route::post('/social_login', 'Auth\SocialLoginController@login')->name('social_login');

// CrudControllers
Route::match(['get', 'post'], '/{model}/{action?}/{any?}/{extra?}', function($model, $action = null, $any = null, $extra = null,$request=null){
    $action = $action ? $action : "index";
    $controller = \App\Http\Classes\CrudController::controller_name($model);

    $data = [];
    if ($any)   $data[] = $any;
    if ($extra)   $data[] = $extra;
    $data[] = request();
    return App::make("\\App\\Http\\Controllers\\".$controller)->callAction($action."_action", $data);
})->name('crud')->middleware("auth");
