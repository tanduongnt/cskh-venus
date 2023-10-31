<?php

namespace App\Extend\Filament\ResourcePages;

use Closure;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Infolist;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Route;
use App\Extend\Filament\NestedResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction as PageEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Actions\CreateAction as TableCreateAction;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;

/**
 * @extends \Filament\Resources\Pages\EditRecord
 * @extends \Filament\Resources\Pages\ViewRecord
 * @extends \Filament\Resources\Pages\ListRecords
 */
trait NestedPage
{
    public array $urlParameters;

    /**
     * @return class-string<\SevendaysDigital\FilamentNestedResources\NestedResource>
     */
    abstract public static function getResource(): string;

    // Khởi tạo các tham số URL
    public function bootNestedPage(): void
    {
        if (empty($this->urlParameters)) {
            $this->urlParameters = $this->getUrlParametersForState();
        }
    }

    // Lấy các tham số URL
    public function mountNestedPage(): void
    {
        if (empty($this->urlParameters)) {
            $this->urlParameters = $this->getUrlParametersForState();
        }
    }

    // Chuyển đổi các tham số URl thành mảng tham số
    protected function getUrlParametersForState(): array
    {
        $parameters = Route::current()->parameters;

        foreach ($parameters as $key => $value) {
            if ($value instanceof Model) {
                $parameters[$key] = $value->getKey();
            }
        }

        return $parameters;
    }

    // xây dựng các danh sách breadcrumb
    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        // Build the nested breadcrumbs.
        $nestedCrumbs = [];
        foreach ($resource::getParentTree(static::getResource()::getParent(), $this->urlParameters) as $i => $nested) {
            // Here we check if we can view and/or edit a record, if not we replace the link with a #.
            // List.
            if ($nested->resource::canViewAny()) {
                $nestedCrumbs[$nested->getListUrl()] = $nested->resource::getBreadcrumb();
            } else {
                $nestedCrumbs[] = $nested->resource::getBreadcrumb();
            }

            // Edit.
            if (($record = $nested->getRecord()) && $nested->resource::canEdit($record)) {
                $nestedCrumbs[$nested->getEditUrl()] = $nested->getBreadcrumbTitle();
            } else {
                $nestedCrumbs[] = $nested->getBreadcrumbTitle();
            }
        }

        // Add the current list entry.
        if ($resource::canViewAny()) {
            $currentListUrl = $resource::getUrl(
                'index',
                $resource::getParentParametersForUrl($resource::getParent(), $this->urlParameters)
            );
            $nestedCrumbs[$currentListUrl] = $resource::getBreadcrumb();
        } else {
            $nestedCrumbs[] = $resource::getBreadcrumb();
        }

        // If it is a view page we need to add the current entry.
        if ($this instanceof ViewRecord) {
            if ($resource::canEdit($this->record)) {
                $nestedCrumbs[$resource::getUrl('edit', $this->urlParameters)] = $this->getRecordTitle();
            } else {
                $nestedCrumbs[] = $this->getTitle();
            }
        }

        // If it is a edit page we need to add the current entry.
        if ($this instanceof EditRecord) {
            if ($resource::canEdit($this->record)) {
                $nestedCrumbs[$resource::getUrl('edit', $this->urlParameters)] = $this->getRecordTitle();
            } else {
                $nestedCrumbs[] = $this->getTitle();
            }
        }

        // Finalize with the current url.
        $breadcrumb = $this->getBreadcrumb();
        if (filled($breadcrumb)) {
            $nestedCrumbs[] = $breadcrumb;
        }

        return $nestedCrumbs;
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var NestedResource $resource */
        $resource = $this::getResource();

        $parent = Str::camel(Str::afterLast($resource::getParent()::getModel(), '\\'));

        // Create the model.
        $model = $this->getModel()::make($data);
        $model->{$parent}()->associate($this->getParentId());
        $model->save();

        return $model;
    }

    protected function getTableQuery(): Builder
    {
        $urlParams = array_values($this->urlParameters);
        $parameter = array_pop($urlParams);

        return static::getResource()::getEloquentQuery($parameter);
    }

    protected function configureEditAction(Tables\Actions\EditAction|EditAction $action): void
    {
        $resource = static::getResource();

        if ($action instanceof Tables\Actions\EditAction) {
            $action
                ->authorize(fn (Model $record): bool => $resource::canEdit($record))
                ->form(fn (): array => $this->getEditFormSchema());

            if ($resource::hasPage('edit')) {
                $action->url(fn (Model $record): string => $resource::getUrl(
                    'edit',
                    [...$this->urlParameters, 'record' => $record]
                ));
            }
        } else {
            $action
                ->authorize($resource::canEdit($this->getRecord()))
                ->record($this->getRecord())
                ->recordTitle($this->getRecordTitle());

            if ($resource::hasPage('edit')) {
                $action->url(fn (): string => static::getResource()::getUrl(
                    'edit',
                    [...$this->urlParameters, 'record' => $this->getRecord()]
                ));

                return;
            }

            $action->form($this->getFormSchema());
        }
    }

    protected function configureCreateAction(CreateAction | Tables\Actions\CreateAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canCreate())
            ->model($this->getModel())
            ->modelLabel($this->getModelLabel())
            ->form(fn (): array => $this->getCreateFormSchema());

        if ($resource::hasPage('create')) {
            $action->url(fn (): string => $resource::getUrl('create', $this->urlParameters));
        }
    }

    protected function configureDeleteAction(DeleteAction | Tables\Actions\DeleteAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canDelete($this->getRecord()))
            ->record($this->getRecord())
            ->recordTitle($this->getRecordTitle())
            ->successRedirectUrl($resource::getUrl('index', $this->urlParameters));
    }

    protected function configureViewAction(ViewAction | Tables\Actions\ViewAction $action): void
    {
        $resource = static::getResource();

        if ($action instanceof Tables\Actions\ViewAction) {
            $action
                ->authorize(fn (Model $record): bool => $resource::canView($record))
                ->infolist(fn (Infolist $infolist): Infolist => $this->infolist($infolist->columns(2)))
                ->form(fn (Form $form): Form => $this->form($form->columns(2)));

            if ($resource::hasPage('view')) {
                $action->url(fn (Model $record): string => $resource::getUrl('view', [...$this->urlParameters, 'record' => $record]));
            }
        } else {
            $action
                ->authorize($resource::canView($this->getRecord()))
                ->record($this->getRecord())
                ->recordTitle($this->getRecordTitle());

            if ($resource::hasPage('view')) {
                $action->url(fn (): string => static::getResource()::getUrl('view', [...$this->urlParameters, 'record' => $this->getRecord()]));

                return;
            }

            $action->form($this->getFormSchema());
        }
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($this->record)) {
            return $resource::getUrl('view', [...$this->urlParameters, 'record' => $this->record]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($this->record)) {
            return $resource::getUrl('edit', [...$this->urlParameters, 'record' => $this->record]);
        }

        return $resource::getUrl('index', $this->urlParameters);
    }

    protected function getParentId(): string|int
    {
        /** @var NestedResource $resource */
        $resource = $this::getResource();

        $parent = Str::camel(Str::afterLast($resource::getParent()::getModel(), '\\'));

        if (array_key_exists('project', $this->urlParameters)) {
            if ($this->urlParameters['project'] instanceof Model) {
                return $this->urlParameters['project']->getKey();
            }
        }

        if (is_array($this->urlParameters[$parent]) && isset($this->urlParameters[$parent]['id'])) {
            return $this->urlParameters[$parent]['id'];
        }

        return $this->urlParameters[$parent];
    }

    public function getParent(): Model
    {
        $resource = $this::getResource();

        return $resource::getParent()::getModel()::find($this->getParentId());
    }

    public function form(Form $form): Form
    {
        return static::getResource()::form($form, $this->getParent());
    }

    // protected function getTableRecordUrlUsing(): ?Closure
    // {
    //     return function (Model $record): ?string {
    //         foreach (['view', 'edit'] as $action) {
    //             $action = $this->getCachedTableAction($action);

    //             if (!$action) {
    //                 continue;
    //             }

    //             $action->record($record);

    //             if ($action->isHidden()) {
    //                 continue;
    //             }

    //             $url = $action->getUrl();

    //             if (!$url) {
    //                 continue;
    //             }

    //             return $url;
    //         }

    //         $resource = static::getResource();

    //         foreach (['view', 'edit'] as $action) {
    //             if (!$resource::hasPage($action)) {
    //                 continue;
    //             }

    //             if (!$resource::{'can' . ucfirst($action)}($record)) {
    //                 continue;
    //             }

    //             return $resource::getUrl($action, [...$this->urlParameters, 'record' => $record]);
    //         }

    //         return null;
    //     };
    // }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable()
            ->query(fn (): Builder => $this->getTableQuery())
            ->modelLabel($this->getModelLabel() ?? static::getResource()::getModelLabel())
            ->pluralModelLabel($this->getPluralModelLabel() ?? static::getResource()::getPluralModelLabel())
            ->recordAction(function (Model $record, Table $table): ?string {
                //foreach (['view', 'view'] as $action) {
                $action = $table->getAction('view');

                // if (!$action) {
                //     continue;
                // }

                $action->record($record);

                // if ($action->isHidden()) {
                //     continue;
                // }

                // if ($action->getUrl()) {
                //     continue;
                // }

                return $action->getName();
                //}

                return null;
            })
            ->recordTitle(fn (Model $record): string => static::getResource()::getRecordTitle($record))
            ->recordUrl($this->getTableRecordUrlUsing() ?? function (Model $record, Table $table): ?string {
                // foreach (['view', 'view'] as $action) {
                $action = $table->getAction('view');

                // if (!$action) {
                //     continue;
                // }

                $action->record($record);

                // if ($action->isHidden()) {
                //     continue;
                // }

                $url = $action->getUrl();

                // if (!$url) {
                //     continue;
                // }

                return $url;
                // }

                $resource = static::getResource();

                // foreach (['view', 'view'] as $action) {
                // if (!$resource::hasPage($action)) {
                //     continue;
                // }

                // if (!$resource::{'can' . ucfirst($action)}($record)) {
                //     continue;
                // }

                return $resource::getUrl('view', ['record' => $record]);
                // }

                return null;
            })
            ->reorderable(condition: static::getResource()::canReorder());
    }
}
