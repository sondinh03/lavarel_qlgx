<?php

namespace App\Http\Controllers\Admin\Concerns;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Chuẩn hóa trang Show Backpack: không dump cả bảng DB, dùng cột list + layout chung.
 */
trait ConfiguresBackpackShow
{
    protected function applyStandardShowSettings(): void
    {
        CRUD::set('show.setFromDb', false);
        CRUD::setShowContentClass('col-md-10 col-lg-8');
    }

    /**
     * Tái sử dụng cột từ setupListOperation cho trang chi tiết.
     */
    protected function setupShowFromListColumns(): void
    {
        $this->applyStandardShowSettings();

        if (method_exists($this, 'setupListOperation')) {
            $this->setupListOperation();
        }

        $this->crud->removeColumns(['blank_first_column', 'bulk_actions']);
    }
}
