<?php
use Illuminate\Support\Facades\Route;

Route::get('/test-categories', function () {
    return view('test-category');
});
