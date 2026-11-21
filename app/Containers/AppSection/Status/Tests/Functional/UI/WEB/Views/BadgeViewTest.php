<?php

namespace App\Containers\AppSection\Status\Tests\Functional\UI\WEB\Views;

use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Providers\StatusServiceProvider;
use App\Containers\AppSection\Status\Tests\FunctionalTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StatusServiceProvider::class)]
final class BadgeViewTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testRendersNameWithPresetColorsForBothThemes(): void
    {
        $html = view('appSection@status::badge', ['name' => 'Offer', 'color' => StatusColor::GREEN])->render();

        $this->assertStringContainsString('class="hb-status-badge"', $html);
        $this->assertStringContainsString('--hb-status-bg: #F0FDF4;', $html);
        $this->assertStringContainsString('--hb-status-border: #BBF7D0;', $html);
        $this->assertStringContainsString('--hb-status-text: #15803D;', $html);
        $this->assertStringContainsString('--hb-status-bg-dark: #22C55E26;', $html);
        $this->assertStringContainsString('--hb-status-border-dark: #22C55E4D;', $html);
        $this->assertStringContainsString('--hb-status-text-dark: #86EFAC;', $html);
        $this->assertStringContainsString('>Offer</span>', $html);
    }

    /**
     * @return void
     */
    public function testEscapesStatusName(): void
    {
        $html = view('appSection@status::badge', ['name' => '<script>alert(1)</script>', 'color' => StatusColor::RED])->render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
    }

    /**
     * @return void
     */
    public function testPanelPagesLoadBadgeStylesheet(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->get('/admin/settings/statuses')
            ->assertOk()
            ->assertSee('css/app/status-badge.css', false);
    }
}
