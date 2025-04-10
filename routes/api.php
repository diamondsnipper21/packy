<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Api\VerifyCommunityApiKey;
use App\Http\Controllers\Api\MemberController;

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

Route::group([
    'prefix' => 'v1'
], function () {
    Route::group([
        'prefix' => 'members',
        'middleware' => [
            VerifyCommunityApiKey::class
        ]
    ], function () {
        Route::get('/', [MemberController::class, 'list']);
        Route::get('/{memberId}', [MemberController::class, 'view']);
        Route::post('/', [MemberController::class, 'create']);
        Route::put('/{memberId}', [MemberController::class, 'update']);
        Route::delete('/{memberId}', [MemberController::class, 'remove']);
    });
});
