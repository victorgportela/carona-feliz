<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

Route::get('/', function () {
    return view('welcome');
});

// Rota para servir imagens de veículos com CORS
Route::get('/storage/vehicle-photos/{filename}', function ($filename) {
    \Log::info('Serving vehicle photo: ' . $filename);
    
    $path = 'vehicle-photos/' . $filename;
    
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    $file = Storage::disk('public')->get($path);
    $mimeType = Storage::disk('public')->mimeType($path);
    
    $response = response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
        ->header('Access-Control-Max-Age', '86400')
        ->header('Cache-Control', 'public, max-age=3600');
    
    \Log::info('Response headers set for CORS');
    
    return $response;
});
