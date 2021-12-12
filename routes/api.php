<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Laravel８式書き方
use App\Http\Controllers\UserController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MuteWordController;
use App\Http\Controllers\MuteUserController;
use App\Http\Controllers\LoginCheckController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestAuthController;
use App\Http\Controllers\RedisReportController;

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

//ログインチェック
Route::get('/check/login', LoginCheckController::class);
//ゲストログイン
Route::post('/login/guest', [GuestAuthController::class, 'guestLogin']);

//認証 Laravel Sanctum
Route::group(['middleware' => ['auth:sanctum']], function () {
    //auth
    Route::get('/users/me', [AuthController::class, 'returnMyId']);
    Route::get('/users/me/info', [AuthController::class, 'returnMyInfo']);
    Route::patch('/users/me', [AuthController::class, 'editAccount']);
    Route::post('/users/me/profile', [AuthController::class, 'editProfile']);
    Route::delete('/users/me', [AuthController::class, 'destroy']);
    //ゲスト用初期化
    Route::get('/users/me/profile', [AuthController::class, 'resetGuestProfile']);

    //users
    Route::get('/users/{user_id}', [UserController::class, 'returnUserInfo'])->whereNumber('user_id');
    Route::get('/exists/users/{user_id}', [UserController::class, 'exists']);

    //threads
    Route::get('/threads', [ThreadController::class, 'index']);
    Route::get('/threads/{thread_id}', [ThreadController::class, 'show'])->whereNumber('thread_id');
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/exists/threads/{thread_id}', [ThreadController::class, 'exists']);
    Route::delete('/threads', [ThreadController::class, 'destroy']);

    //posts
    Route::get('/posts/', [PostController::class, 'index']);
    Route::get('/posts/paginated', [PostController::class, 'returnPaginator']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/posts/edit', [PostController::class, 'edit']);
    Route::delete('/posts', [PostController::class, 'destroy']);

    //responses
    Route::get('/exists/threads/{thread_id}/responses/{displayed_post_id}', [ResponseController::class, 'exists']);

    //images
    Route::get('/images/threads', [ImageController::class, 'returnThreadImages']);
    //lightbox
    Route::get('/images/threads/{thread_id}', [ImageController::class, 'returnImagesForTheThread'])->whereNumber('thread_id');
    Route::get('/images/threads/{thread_id}/responses/{displayed_post_id}', [ImageController::class, 'returnImagesForTheResponses'])
        ->whereNumber('thread_id')->whereNumber('displayed_post_id');
    Route::get('/images/users/{user_id}/post', [ImageController::class, 'returnImagesTheUserPosted'])->whereNumber('user_id');
    Route::get('/images/users/{user_id}/like', [ImageController::class, 'returnImagesTheUserLiked'])->whereNumber('user_id');
    Route::get('/images/search', [ImageController::class, 'returnImagesForTheSearch']);

    //mute word
    Route::get('/mute_words', [MuteWordController::class, 'index']);
    Route::post('/mute_words', [MuteWordController::class, 'store']);
    Route::delete('/mute_words', [MuteWordController::class, 'destroy']);

    //mute user
    Route::get('/mute_users', [MuteUserController::class, 'index']);
    Route::put('/mute_users', [MuteUserController::class, 'store']);
    Route::delete('/mute_users', [MuteUserController::class, 'destroy']);

    //like
    Route::put('/like', [LikeController::class, 'store']);
    Route::delete('/like', [LikeController::class, 'destroy']);

    //redis
    Route::get('/report/overview/{year_month}', [RedisReportController::class, 'returnMonthlyOverview']);
    Route::get('/dashboard/active_user_count', [RedisReportController::class, 'returnActiveUserCount']);
});










//404
Route::any('{any}', function () {
    return response()->json([
        'status'    => 404,
        'message'   => 'Not Found.',
    ], 404);
})->where('any', '.*');
