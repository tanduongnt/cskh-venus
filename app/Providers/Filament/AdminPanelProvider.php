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
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Admin\Resources\BuildingResource;
use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Admin\Resources\UserResource;
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
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Cài đặt'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->pages([
                Dashboard::class,
            ])
            ->navigationItems([
                NavigationItem::make('buildings')
                    ->url(fn (): string => BuildingResource::getUrl())
                    ->icon('heroicon-o-building-office-2')
                    ->label('Chung cư'),
                NavigationItem::make('customer')
                    ->url(fn (): string => CustomerResource::getUrl())
                    ->icon('bi-people-fill')
                    ->label('Khách hàng'),
                //Cài đặt
                NavigationItem::make('utilityTypes')
                    ->group('Cài đặt')
                    ->url(fn (): string => UtilityTypeResource::getUrl())
                    ->icon('heroicon-o-presentation-chart-line')
                    ->label('Loại tiện ích'),
                NavigationItem::make('users')
                    ->group('Cài đặt')
                    ->url(fn (): string => UserResource::getUrl())
                    ->icon('bi-person-circle')
                    ->label('Nhân viên'),
            ])
            ->widgets([])
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
