<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/migrate', function() {
    try {
        Artisan::call('migrate');

        return Response::json([
            'message' => 'Migraciones ejecutadas correctamente.',
            'output' => Artisan::output()
        ], 200);
    } catch (\Exception $e) {
        return Response::json([
            'message' => 'Error al ejecutar migraciones.',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::post('register', [AuthController::class, 'register']);
Route::get('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->put('/user/update', [AuthController::class, 'updateProfile']);
