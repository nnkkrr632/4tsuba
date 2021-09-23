<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Laravel８式書き方
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\MuteWordController;
use App\Http\Controllers\MuteUserController;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestAuthController;


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
//検証
Route::get('/test', [MuteWordController::class, 'addHasMuteWordKeyToPosts']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
//ゲストユーザーログイン用
Route::get('/login/guest/{user_id}', [GuestAuthController::class, 'guestLogin']);

//検証用 ConfirmLoginComponent
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/check', [AuthController::class, 'checkLoginOrNot']);


//タスク
Route::get('/tasks', [TaskController::class, 'index']);
Route::get('/tasks/{task}', [TaskController::class, 'show']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::put('/tasks/{task}', [TaskController::class, 'update']);
Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    //auth sanctum 通すURLはこの中入れる
});
//開発のため一旦外だし
// --------------------------------------------------------------------
//クエリパラメータはルーティングに書かずとも$requestで取得できる
//例.  /threads?sort=2?desc=1  は $request->sort $request->desc

//auth
Route::get('/users/me', [AuthController::class, 'returnMyId']);
Route::get('/users/me/info', [AuthController::class, 'returnMyInfo']);
Route::patch('/users/me', [AuthController::class, 'editAccount']);
Route::post('/users/me/profile', [UserController::class, 'editProfile']);
Route::delete('/users/me', [AuthController::class, 'destroy']);

//ゲスト用初期化
Route::get('/users/me/profile', [UserController::class, 'resetGuestProfile']);

//users
Route::get('/users', [UserController::class, 'returnUserInfo']);
Route::get('/exists/users/{user_id}', [UserController::class, 'exists']);


//mute word
Route::get('/mute_words', [MuteWordController::class, 'index']);
Route::post('/mute_words', [MuteWordController::class, 'store']);
Route::delete('/mute_words', [MuteWordController::class, 'destroy']);
//mute user
Route::get('/mute_users', [MuteUserController::class, 'index']);
Route::put('/mute_users', [MuteUserController::class, 'store']);
Route::delete('/mute_users', [MuteUserController::class, 'destroy']);



//threads
Route::get('/threads', [ThreadController::class, 'index']);
Route::get('/threads/{thread_id}', [ThreadController::class, 'show']);
Route::post('/threads', [ThreadController::class, 'store']);
Route::get('/exists/threads/{thread_id}', [ThreadController::class, 'exists']);
Route::delete('/threads', [ThreadController::class, 'destroy']);


//posts
Route::get('/posts/', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::post('/posts/edit', [PostController::class, 'edit']);
Route::delete('/posts', [PostController::class, 'destroy']);

//responses
Route::get('/exists/threads/{thread_id}/responses/{displayed_post_id}', [ResponseController::class, 'exists']);

//images
Route::get('/images/threads', [ImageController::class, 'returnThreadImages']);
//lightbox
Route::get('/images/threads/{thread_id}', [ImageController::class, 'returnImagesForTheThread']);
Route::get('/images/users/{user_id}/post', [ImageController::class, 'returnImagesTheUserPosted']);
Route::get('/images/users/{user_id}/like', [ImageController::class, 'returnImagesTheUserLiked']);
Route::get('/images/search', [ImageController::class, 'returnImagesForTheSearch']);

//like
Route::put('/like', [LikeController::class, 'store']);
Route::delete('/like', [LikeController::class, 'destroy']);
// --------------------------------------------------------------------
