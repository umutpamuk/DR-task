<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\OrderController;
use \App\Http\Controllers\Api\CampaignController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::post('/', 'store');
    Route::get('/{orderNumber}', 'show');
});

Route::get('/campaigns', [CampaignController::class, 'index']);
