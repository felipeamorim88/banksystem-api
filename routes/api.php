<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FinancialMovementController;


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
//Login/SignUp Routes
Route::post('user/login', [UserController::class, 'Login']);
Route::post('user/signup', [UserController::class, 'SignUp']);
//Client Routes
Route::post('account', [AccountController::class, 'Post'])->middleware('jwt.auth');
Route::post('account/verification', [AccountController::class, 'CheckAccount'])->middleware('jwt.auth');
Route::post('account/deposit', [AccountController::class, 'Deposit'])->middleware('jwt.auth');
Route::get('financial-movement/user/{id}', [FinancialMovementController::class, 'GetByUser'])->middleware('jwt.auth');
Route::post('purchasing', [FinancialMovementController::class, 'Purchasing'])->middleware('jwt.auth');
//Admin routes
//Financial-movement
Route::get('financial-movement', [FinancialMovementController::class, 'GetByAdmin'])
    ->middleware('jwt.auth')->middleware('can:manage_users');

Route::get('financial-movement/pending', [FinancialMovementController::class, 'GetPending'])
    ->middleware('jwt.auth')->middleware('can:manage_users');
//Accept/Reject transaction
Route::post('account/transaction/accept/{id}', [AccountController::class, 'Accept'])
    ->middleware('jwt.auth')->middleware('can:manage_users');

Route::post('account/transaction/reject/{id}', [AccountController::class, 'Reject'])
    ->middleware('jwt.auth')->middleware('can:manage_users');