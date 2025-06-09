<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\SavedGameController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TwitterController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;

// Ruta de prueba para verificar que la API está activa
Route::get('/test-api-enabled', function () {
    return response()->json(['message' => 'API is enabled']);
});

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'handleGoogleLogin']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/guides/all', [GuideController::class, 'all']);
Route::get('/guides/{id}', [GuideController::class, 'show']);
Route::get('/videos', [VideoController::class, 'index']); // Public route for listing videos
Route::get('/videos/{id}', [VideoController::class, 'show']); // Public route for showing a single video

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de usuario
    Route::get('/user', [UserController::class, 'getUser']);
    Route::get('/user/comments', [UserController::class, 'getComments']);
    Route::post('/upload-avatar', [UserController::class, 'uploadAvatar']);
    Route::put('/user/tier', [UserController::class, 'updateTier']);

    // Rutas de autenticación
    Route::put('/user', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas de juego guardado
    Route::post('/save-game', [SavedGameController::class, 'store']);
    Route::get('/load-game', [SavedGameController::class, 'show']);

    // Rutas para guías
    Route::get('/guides', [GuideController::class, 'index']);
    Route::post('/guides', [GuideController::class, 'store']);
    Route::put('/guides/{guide}', [GuideController::class, 'update']);
    Route::delete('/guides/{guide}', [GuideController::class, 'destroy']);
    Route::post('/guides/{id}/like', [GuideController::class, 'like']);
    Route::post('/guides/{id}/dislike', [GuideController::class, 'dislike']);

    // Rutas para capturas
    Route::get('/screenshots', [ScreenshotController::class, 'index']);
    Route::post('/screenshots', [ScreenshotController::class, 'store']);
    Route::delete('/screenshots/{screenshot}', [ScreenshotController::class, 'destroy']);
    Route::get('/screenshots/{screenshot}/comments', [ScreenshotController::class, 'getComments']);
    Route::post('/screenshots/{screenshot}/comments', [ScreenshotController::class, 'storeComment']);
    Route::delete('/screenshots/{screenshot}/comments/{comment}', [ScreenshotController::class, 'destroyComment']);
    Route::get('/screenshots/{id}', [ScreenshotController::class, 'show']);
    Route::post('/screenshots/{id}/like', [ScreenshotController::class, 'like']);
    Route::post('/screenshots/{id}/dislike', [ScreenshotController::class, 'dislike']);

    // Rutas para videos
    Route::get('/videos', [VideoController::class, 'index']);
    Route::post('/videos', [VideoController::class, 'store']);
    Route::get('/videos/{id}', [VideoController::class, 'show']);
    Route::delete('/videos/{video}', [VideoController::class, 'destroy']);
    Route::get('/videos/{id}/comments', [VideoController::class, 'comments']);
    Route::post('/videos/{id}/comments', [VideoController::class, 'storeComment']);
    Route::delete('/videos/{video}/comments/{comment}', [VideoController::class, 'destroyComment']);
    Route::post('/videos/{id}/like', [VideoController::class, 'like']);
    Route::post('/videos/{id}/dislike', [VideoController::class, 'dislike']);

    // Rutas para comentarios
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Rutas para verificación de email
    Route::post('/send-verification', [VerificationController::class, 'sendVerificationEmail']);
    Route::get('/verify-email/{token}', [VerificationController::class, 'verifyEmail']);

    Route::middleware('auth:sanctum')->post('/subscribe', [UserController::class, 'subscribe']);


    Route::get('/guides/{id}/ratings', [GuideController::class, 'ratings']);

});