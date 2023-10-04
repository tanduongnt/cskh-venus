<?php

namespace App\Extend\Filament;

use Illuminate\Database\Eloquent\Model;

class NestedEntry
{
    public function __construct(
        public string $urlPlaceholder,
        public string $urlPart,
        /** @var class-string<\Filament\Resources\Resource> $resource */
        public string $resource,
        public string $label,
        public null|string|int $id,
        public array $urlParams,
    ) {
    }

    //Phương thức trả về URL của danh sách các bản ghi con
    public function getListUrl(): string
    {

        $params = $this->urlParams;
        array_pop($params);

        return $this->resource::getUrl('index', $params);
    }

    // Phương thức trả về URL để chỉnh sửa bản ghi con cụ thể
    public function getEditUrl(): string
    {
        $params = $this->urlParams;
        array_pop($params);
        return $this->resource::getUrl('edit', [...$params, 'record' => $this->id()]);
    }

    // Phương thức trả về một bản ghi con cụ thể dựa trên ID.
    public function getRecord(): ?Model
    {
        return $this->resource::resolveRecordRouteBinding($this->id);
    }

    //Phương thức trả về tiêu đề breadcrumb cho bản ghi con
    public function getBreadcrumbTitle(): string
    {
        return $this->resource::getRecordTitle($this->resource::getModel()::find($this->id()));
    }

    private function id(): string|int|null
    {
        return $this->id;
    }
}
