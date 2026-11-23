<?php

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\UI\WEB\Controllers\PreviewResumeController;
use Illuminate\Support\Facades\Route;

Route::get('admin/profile/resume/{locale}/preview', PreviewResumeController::class)
    ->whereIn('locale', ProfileLocale::values())
    ->middleware('auth')
    ->name('profile.resume.preview');
