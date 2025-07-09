<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\AdministrateurController;
use App\Http\Controllers\ProprietaireController;
use App\Http\Controllers\LocataireController;
use App\Http\Controllers\AppartementController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ModePaiementController;

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

// Routes pour les utilisateurs
Route::prefix('utilisateur')->group(function () {
    Route::get('/', [UtilisateurController::class, 'index']);
    Route::post('/', [UtilisateurController::class, 'store']);
    Route::get('/count', [UtilisateurController::class, 'count']);
    Route::get('/admin', [UtilisateurController::class, 'getAdmin']);
    Route::get('/email/{email}', [UtilisateurController::class, 'getByEmail']);
    Route::get('/identifiant/{identifiant}', [UtilisateurController::class, 'getByIdentifiant']);
    Route::get('/exact-identifiant/{identifiant}', [UtilisateurController::class, 'getUserByIdentifiant']);
    Route::get('/{id}', [UtilisateurController::class, 'show']);
    Route::put('/{id}', [UtilisateurController::class, 'update']);
    Route::delete('/{id}', [UtilisateurController::class, 'destroy']);
    Route::post('/login', [UtilisateurController::class, 'login']);
});

// Routes pour les administrateurs
Route::prefix('administrateur')->group(function () {
    Route::get('/', [AdministrateurController::class, 'index']);
    Route::post('/', [AdministrateurController::class, 'store']);
    Route::get('/{id}', [AdministrateurController::class, 'show']);
    Route::put('/{id}', [AdministrateurController::class, 'update']);
    Route::delete('/{id}', [AdministrateurController::class, 'destroy']);
});

// Routes pour les propriétaires
Route::prefix('proprietaire')->group(function () {
    Route::get('/', [ProprietaireController::class, 'index']);
    Route::post('/', [ProprietaireController::class, 'store']);
    Route::get('/{id}', [ProprietaireController::class, 'show']);
    Route::put('/{id}', [ProprietaireController::class, 'update']);
    Route::delete('/{id}', [ProprietaireController::class, 'destroy']);
});

// Routes pour les locataires
Route::prefix('locataire')->group(function () {
    Route::get('/', [LocataireController::class, 'index']);
    Route::post('/', [LocataireController::class, 'store']);
    Route::get('/{id}', [LocataireController::class, 'show']);
    Route::put('/{id}', [LocataireController::class, 'update']);
    Route::delete('/{id}', [LocataireController::class, 'destroy']);
});

// Routes pour les appartements
Route::prefix('appartement')->group(function () {
    Route::get('/', [AppartementController::class, 'index']);
    Route::post('/', [AppartementController::class, 'store']);
    Route::get('/disponibles', [AppartementController::class, 'getDisponibles']);
    Route::get('/{id}', [AppartementController::class, 'show']);
    Route::put('/{id}', [AppartementController::class, 'update']);
    Route::delete('/{id}', [AppartementController::class, 'destroy']);
});

// Routes pour les locations
Route::prefix('location')->group(function () {
    Route::get('/', [LocationController::class, 'index']);
    Route::post('/', [LocationController::class, 'store']);
    Route::get('/{id}', [LocationController::class, 'show']);
    Route::put('/{id}', [LocationController::class, 'update']);
    Route::delete('/{id}', [LocationController::class, 'destroy']);
});

// Routes pour les paiements
Route::prefix('paiement')->group(function () {
    Route::get('/', [PaiementController::class, 'index']);
    Route::post('/', [PaiementController::class, 'store']);
    Route::get('/{id}', [PaiementController::class, 'show']);
    Route::put('/{id}', [PaiementController::class, 'update']);
    Route::delete('/{id}', [PaiementController::class, 'destroy']);
});

// Routes pour les modes de paiement
Route::prefix('modepaiement')->group(function () {
    Route::get('/', [ModePaiementController::class, 'index']);
    Route::post('/', [ModePaiementController::class, 'store']);
    Route::get('/{id}', [ModePaiementController::class, 'show']);
    Route::put('/{id}', [ModePaiementController::class, 'update']);
    Route::delete('/{id}', [ModePaiementController::class, 'destroy']);
}); 