<?php

namespace App\Extend\Filament\Columns;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use App\Extend\Filament\NestedResource;

class ChildResourceLink extends TextColumn
{
    /**
     * @var NestedResource
     */
    private string $resourceClass;

    /**
     * @param  NestedResource  $name
     */

    // Đây là phương thức tạo ví dụ của childResouceLink với tên định danh và thiết lập giá trị
    public static function make(string $name): static
    {
        $item = parent::make($name);
        $item->forResource($name);
        $item->label($item->getChildLabelPlural());

        return $item;
    }

    // Phương thức trả về nhãn số nhiều trên bản ghi con
    public function getChildLabelPlural(): string
    {
        return Str::title($this->resourceClass::getPluralModelLabel());
    }

    // Phương thức trả về nhãn số ít trên bản ghi con
    public function getChildLabelSingular(): string
    {
        return Str::title($this->resourceClass::getModelLabel());
    }


    // Phương thức thiết lập giá trị cho thuộc tính $resouceClass
    public function forResource(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    //Phương thức trả về trạng thái của cột (bao gồm số lượng bản ghi con của bản ghi hiện tại và nhãn phù hợp)
    public function getState(): string
    {
        $count = $this->getCount();

        //$state = 'Hệ thống';

        //return $state;
        return $this->getChildLabelPlural();
    }

    //Phương thức trả về URL cho cột, dựa trên các tham số và giá trị của bản ghi hiện tại.
    public function getUrl(): ?string
    {
        $baseParams = [];
        if (property_exists($this->table->getLivewire(), 'urlParameters')) {
            $baseParams = $this->table->getLivewire()->urlParameters;
        }

        $parameters = Str::camel(Str::singular($this->resourceClass::getParent()::getSlug()));
        // dd($parameters, $this->resourceClass::getUrl(
        //     'index',
        //     [...$baseParams]
        // ));
        return $this->resourceClass::getUrl(
            'index',
            [...$baseParams, $parameters => $this->record->getKey()]
        );
    }

    //Phương thức trả về số lượng bản ghi con của bản ghi hiện tại
    private function getCount(): int
    {
        return $this->resourceClass::getEloquentQuery($this->record->getKey())->count();
    }

}
