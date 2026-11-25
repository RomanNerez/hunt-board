<?php

use App\Containers\AppSection\Resume\UI\WEB\Controllers\DownloadResumePdfController;
use Illuminate\Support\Facades\Route;

Route::get('admin/resumes/{resume}/pdf/download', DownloadResumePdfController::class)
    ->whereNumber('resume')
    ->middleware('auth')
    ->name('resumes.pdf.download');
