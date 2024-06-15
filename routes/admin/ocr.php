<?php

use App\Http\Controllers\Admin\OcrController;

Route::post('projects/{projects}/ocr', [OcrController::class, 'ocr'])->name('admin.projects.ocr');
Route::post('projects/{projects}/expeditions/{expeditions}/ocr', [OcrController::class, 'ocr'])->name('admin.expeditions.ocr');

