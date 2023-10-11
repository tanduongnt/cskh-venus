<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use App\Models\Building;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Admin\Pages\Customer;
use App\Filament\Admin\Pages\Dashboard;
use Filament\Navigation\NavigationItem;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Admin\Resources\BuildingResource;
use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Admin\Resources\UtilityTypeResource;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->navigationItems([
                NavigationItem::make('buildings')
                    ->url(fn (): string => BuildingResource::getUrl())
                    ->icon('heroicon-o-building-office-2')
                    ->sort(3)
                    ->label('Chung cư'),
                NavigationItem::make('utilityTypes')
                    ->url(fn (): string => UtilityTypeResource::getUrl())
                    ->icon('heroicon-o-presentation-chart-line')
                    ->sort(3)
                    ->label('Loại tiện ích'),
                NavigationItem::make('customer')
                    ->url(fn (): string => CustomerResource::getUrl())
                    ->icon('heroicon-o-user-circle')
                    ->sort(3)
                    ->label('Khách hàng'),
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
