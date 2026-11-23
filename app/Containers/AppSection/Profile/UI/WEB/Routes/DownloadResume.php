<?php

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\UI\WEB\Controllers\DownloadResumeController;
use Illuminate\Support\Facades\Route;

Route::get('admin/profile/resume/{locale}/download', DownloadResumeController::class)
    ->whereIn('locale', ProfileLocale::values())
    ->middleware('auth')
    ->name('profile.resume.download');
