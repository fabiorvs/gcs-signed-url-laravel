<?php

use App\Http\Controllers\HtmlController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/html/{filename}', [HtmlController::class, 'serveHtml'])->where('filename', '.*');
Route::get('/redirect/{filename}', [HtmlController::class, 'redirectFile'])->where('filename', '.*');
