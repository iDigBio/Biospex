<?php

use App\Http\Controllers\Admin\OcrController;

Route::post('projects/{project}/ocr', [OcrController::class, 'index'])->name('admin.projects.ocr');
Route::post('expeditions/{expedition}/ocr', [OcrController::class, 'index'])->name('admin.expeditions.ocr');
