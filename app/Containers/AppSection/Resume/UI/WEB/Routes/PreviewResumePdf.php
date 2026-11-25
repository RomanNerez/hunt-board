<?php

use App\Containers\AppSection\Resume\UI\WEB\Controllers\PreviewResumePdfController;
use Illuminate\Support\Facades\Route;

Route::get('admin/resumes/{resume}/pdf/preview', PreviewResumePdfController::class)
    ->whereNumber('resume')
    ->middleware('auth')
    ->name('resumes.pdf.preview');
