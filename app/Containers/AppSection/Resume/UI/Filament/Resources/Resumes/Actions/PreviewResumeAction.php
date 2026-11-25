<?php

namespace App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Actions;

use App\Containers\AppSection\Resume\Models\Resume;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

final class PreviewResumeAction
{
    /**
     * @var string
     */
    private const MODAL_FRAME_HEIGHT = 'calc(100vh - 10rem)';

    /**
     * @return Action
     */
    public static function make(): Action
    {
        return Action::make('preview')
            ->label('Переглянути')
            ->tooltip('Переглянути')
            ->icon(Heroicon::OutlinedEye)
            ->iconButton()
            ->color('gray')
            ->slideOver()
            ->modalWidth(Width::FiveExtraLarge)
            ->modalHeading(static fn (Resume $record): string => "{$record->first_name} {$record->last_name}")
            ->modalContent(static fn (Resume $record): View => self::frame($record, self::MODAL_FRAME_HEIGHT))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Закрити')
            ->extraModalFooterActions(static fn (Resume $record): array => [
                self::downloadAction($record),
                self::openInNewTabAction($record),
            ]);
    }

    /**
     * @param Resume $resume
     * @param string $height
     *
     * @return View
     */
    public static function frame(Resume $resume, string $height): View
    {
        return view('appSection@resume::preview-frame', [
            'previewUrl' => self::previewUrl($resume),
            'height' => $height,
        ]);
    }

    /**
     * @param Resume $resume
     *
     * @return Action
     */
    public static function downloadAction(Resume $resume): Action
    {
        return Action::make('downloadPdf')
            ->label('Завантажити PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->url(route('resumes.pdf.download', ['resume' => $resume->id]));
    }

    /**
     * @param Resume $resume
     *
     * @return Action
     */
    public static function openInNewTabAction(Resume $resume): Action
    {
        return Action::make('openPdf')
            ->label('Відкрити в новій вкладці')
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->color('gray')
            ->url(self::previewUrl($resume))
            ->openUrlInNewTab();
    }

    /**
     * @param Resume $resume
     *
     * @return string
     */
    public static function previewUrl(Resume $resume): string
    {
        return route('resumes.pdf.preview', ['resume' => $resume->id]);
    }
}
