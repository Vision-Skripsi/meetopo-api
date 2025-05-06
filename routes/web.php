<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response('Welcome to Meetopo API!', 200)
        ->header('Content-Type', 'text/plain');
});
