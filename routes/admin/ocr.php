<?php

use App\Http\Controllers\Admin\OcrController;

Route::post('projects/{projects}/ocr', [OcrController::class, 'index'])->name('admin.projects.ocr');
Route::post('projects/{projects}/expeditions/{expeditions}/ocr', [OcrController::class, 'index'])->name('admin.expeditions.ocr');

