<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use App\Models\User;
use Filament\Widgets;
use App\Models\Building;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Filament\Admin\Pages\Customer;
use App\Filament\Admin\Pages\Dashboard;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use App\Filament\Admin\Resources\UserResource;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Admin\Pages\RegistrationUtility;
use App\Filament\Admin\Pages\UtilityRegistrationPage;
use App\Filament\Admin\Resources\BuildingResource;
use App\Filament\Admin\Resources\CustomerResource;
use App\Filament\Admin\Resources\RegistrationResource;
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
            ->viteTheme('resources/css/app.css')
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
                    ->label('Chung cư')
                    ->hidden(fn (): bool => !can('building.view')),
                NavigationItem::make('customers')
                    ->url(fn (): string => CustomerResource::getUrl())
                    ->icon('bi-people-fill')
                    ->label('Khách hàng')
                    ->hidden(fn (): bool => !can('customer.view')),
                NavigationItem::make('utility_register')
                    ->url(fn (): string => UtilityRegistrationPage::getUrl())
                    ->icon('bi-file-text-fill')
                    ->sort(3)
                    ->label('Đăng ký tiện ích'),
                NavigationItem::make('utility_invoices')
                    ->url(fn (): string => RegistrationResource::getUrl())
                    ->icon('bi-layout-text-sidebar-reverse')
                    ->sort(4)
                    ->label('Phiếu thu tiện ích'),
                //Cài đặt
                NavigationItem::make('utility_types')
                    ->group('Cài đặt')
                    ->url(fn (): string => UtilityTypeResource::getUrl())
                    ->icon('heroicon-o-presentation-chart-line')
                    ->label('Loại tiện ích')
                    ->hidden(fn (): bool => !can('utility_type.view')),
                NavigationItem::make('users')
                    ->group('Cài đặt')
                    ->url(fn (): string => UserResource::getUrl())
                    ->icon('bi-person-circle')
                    ->label('Nhân viên')
                    ->hidden(fn (): bool => !can('users.view')),
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

    public function boot()
    {
        Gate::before(function (User $user, string $ability) {
            return $user->isSuperAdmin() ? true : null;
        });
    }
}
