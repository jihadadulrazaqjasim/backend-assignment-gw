<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


// public routes
Route::get('/employees', [UserController::class, 'index']);
Route::post('/employees', [UserController::class, 'store']);

//protected routes
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/employees/{id}', [UserController::class, 'show']); //show
    Route::get('employees/search/{q}', [UserController::class, 'searchEmployee']);
    Route::post('employees/export', [UserController::class, 'employeesExportCsv']);

    Route::post('/logout', [UserController::class, 'logout']);

    Route::put('/employees/{id}', [UserController::class, 'update']);
    Route::delete('employees/{id}', [UserController::class, 'destroy']);

    Route::get('/employees/{id}/managers', [UserController::class, 'employeeManagers']);
    Route::get('/employees/{id}/managers-salary', [UserController::class, 'employeeManagersSalary']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {

//     return $request->user();
// });
