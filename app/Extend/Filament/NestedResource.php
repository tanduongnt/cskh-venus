<?php

namespace App\Extend\Filament;

use Closure;
use Filament\Panel;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Exceptions\UrlGenerationException;

abstract class NestedResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static bool $shouldRegisterNavigationWhenInContext = true;

    /**
     * @return resource|NestedResource
     */

    // Xác định tài nguyên cha của tài nguyên hiện tại. Phương thức này trả về tên class của tài nguyên cha
    abstract public static function getParent(): string;

    // Trả về một chuỗi tên truy cập cho tài nguyên cha dựa trên class của nó (Sử dụng getParent() để lấy tên class của tài nguyên cha)
    public static function getParentAccessor(): string
    {
        return Str::of(static::getParent()::getModel())
            ->afterLast('\\Models\\')
            ->camel();
    }

    // Phương thức này trả về Id của tài nguyên cha dựa trên request hiện tại
    public static function getParentId(): int|string|null
    {
        //lấy ID từ route parameter của tài nguyên cha bằng cách sử dụng getParentAccessor() và Route::current()->parameter().
        $parentId = Route::current()->parameter(static::getParentAccessor(), Route::current()->parameter('record'));

        return $parentId instanceof Model ? $parentId->getKey() : $parentId;
    }

    // Phương thức này trả về một query builder để truy vấn dữ liệu từ tài nguyên hiện tại
    public static function getEloquentQuery(string|int|null $parent = null): Builder
    {
        $query = parent::getEloquentQuery();
        $parentModel = static::getParent()::getModel();
        $key = (new $parentModel())->getKeyName();
        $query->whereHas(
            static::getParentAccessor(),
            fn (Builder $builder) => $builder->where($key, '=', $parent ?? static::getParentId())
        );

        return $query;
    }

    // sử dụng để đăng kí các route cho tài nguyên hiện tại
    public static function getRoutes(): Closure
    {
        return function () {
            $slug = static::getSlug();

            $prefix = '';
            foreach (static::getParentTree(static::getParent()) as $parent) {
                $prefix .= $parent->urlPart . '/{' . $parent->urlPlaceholder . '}/';
            }

            Route::name("$slug.")
                ->prefix($prefix . $slug)
                ->middleware(static::getMiddlewares())
                ->group(function () {
                    foreach (static::getPages() as $name => $page) {
                        Route::get($page['route'], $page['class'])->name($name);
                    }
                });
        };
    }

    public static function routes(Panel $panel): void
    {
        $slug = static::getSlug();

        $prefix = '';
        foreach (static::getParentTree(static::getParent()) as $parent) {
            $prefix .= $parent->urlPart . '/{' . $parent->urlPlaceholder . '}/';
        }

        Route::name(
            (string) str($slug)
                ->replace('/', '.')
                ->append('.'),
        )
            ->prefix($prefix . $slug)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->group(function () use ($panel) {
                foreach (static::getPages() as $name => $page) {
                    $page->registerRoute($panel)?->name($name);
                }
            });
            //dd($r);
    }

    // Phương thức mở rộng của getUrl() của class Resource và xử lý thêm các tham số cho tài nguyên cha và bản ghi hiện tại
    public static function getUrl($name = 'index', $parameters = [], $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        $list = static::getParentParametersForUrl(static::getParent(), $parameters);

        $parameters = [...$parameters, ...$list];

        $childParams = Route::current()->parameters();

        if (isset($childParams['record'])) {
            /** @var Page $controller */
            $controller = Route::current()->getController();
            /** @var resource $resource */
            $resource = $controller::getResource();

            $slug = $resource::getSlug();
            $record = $childParams['record'];

            if ($slug && $record) {
                $parameters[Str::singular($slug)] = $record;
            }
        }
        return parent::getUrl($name, [...$childParams, ...$parameters], $isAbsolute);
    }

    /**
     * @return NestedEntry[]
     */

    // Trả về một mảng các NestedEntry đại diện cho cây các tài nguyên cha của tài nguyên hiện tại
    public static function getParentTree(string $parent, array $urlParams = []): array
    {
        // Mỗi NestedEntry chứa thông tin như tên URL, tài nguyên, và ID của tài nguyên cha.
        /** @var $parent Resource|NestedResource */
        $singularSlug = Str::camel(Str::singular($parent::getSlug()));

        $list = [];
        if (new $parent() instanceof NestedResource) {
            $list = [...$list, ...static::getParentTree($parent::getParent(), $urlParams)];
        }

        $urlParams = static::getParentParametersForUrl($parent, $urlParams);

        $id = Route::current()?->parameter(
            $singularSlug,
            $urlParams[$singularSlug] ?? null
        );

        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $list[$parent::getSlug()] = new NestedEntry(
            urlPlaceholder: Str::camel(Str::singular($parent::getSlug())),
            urlPart: $parent::getSlug(),
            resource: $parent,
            label: $parent::getPluralModelLabel(),
            id: $id,
            urlParams: $urlParams
        );

        return $list;
    }

    // Trả về các tham số (parameters) của URL cho các tài nguyên cha dựa trên route hiện tại.
    public static function getParentParametersForUrl(string $parent, array $urlParameters = []): array
    {
        /** @var $parent Resource|NestedResource */
        $list = [];
        $singularSlug = Str::camel(Str::singular($parent::getSlug()));
        if (new $parent() instanceof NestedResource) {
            $list = static::getParentParametersForUrl($parent::getParent(), $urlParameters);
        }
        $list[$singularSlug] = Route::current()?->parameter(
            $singularSlug,
            $urlParameters[$singularSlug] ?? null
        );

        foreach ($list as $key => $value) {
            if ($value instanceof Model) {
                $list[$key] = $value->getKey();
            }
        }

        return $list;
    }

    // Trả về tên nhóm trong menu của tài nguyên hiện tại. Nếu tài nguyên hiện tại có ID của tài nguyên cha, nó sẽ trả về một nhãn dựa trên phương thức getRecordTitle() của tài nguyên cha.
    public static function getNavigationGroup(): ?string
    {
        if (static::getParentId()) {
            return static::getParent()::getRecordTitle(
                static::getParent()::getModel()::find(
                    static::getParentId()
                )
            );
        }

        return static::getParent()::getModelLabel();
    }

    // xác định xem tài nguyên hiện tại có nên đăng ký vào menu hay không.
    public static function shouldRegisterNavigation(): bool
    {
        if (static::$shouldRegisterNavigationWhenInContext) {
            try {
                static::getUrl('index');

                return true;
            } catch (UrlGenerationException) {
                return false;
            }
        }

        return parent::shouldRegisterNavigation();
    }
}
