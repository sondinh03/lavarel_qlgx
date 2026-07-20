@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
        trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
        $crud->entity_name_plural => url($crud->route),
        trans('backpack::crud.preview') => false,
    ];
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;

    $entryTitle = $entry->name
        ?? $entry->title
        ?? $entry->email
        ?? $entry->code
        ?? $entry->reference_code
        ?? ('#'.$entry->getKey());

    $entrySubtitle = $crud->entity_name;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <h2 class="mb-0">
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>{!! $crud->getSubheading() ?? mb_ucfirst(trans('backpack::crud.preview')).' '.$crud->entity_name !!}</small>
            @if ($crud->hasAccess('list'))
                <small>
                    <a href="{{ url($crud->route) }}" class="font-sm">
                        <i class="la la-angle-double-left"></i>
                        {{ trans('backpack::crud.back_to_all') }}
                        <span>{{ $crud->entity_name_plural }}</span>
                    </a>
                </small>
            @endif
        </h2>
        <div class="d-flex align-items-center" style="gap: 0.5rem;">
            <a href="javascript:window.print();" class="btn btn-sm btn-outline-secondary" title="In">
                <i class="la la-print"></i>
            </a>
            @if ($crud->hasAccess('update'))
                <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-sm btn-primary">
                    <i class="la la-edit"></i> Sửa
                </a>
            @endif
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="{{ $crud->getShowContentClass() }}">

        @if ($crud->model->translationEnabled())
            <div class="mb-3 text-right">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ trans('backpack::crud.language') }}:
                        {{ $crud->model->getAvailableLocales()[request()->input('locale') ?: app()->getLocale()] }}
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                            <a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}?locale={{ $key }}">{{ $locale }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Header card --}}
        <div class="card mb-3">
            <div class="card-body py-4 px-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0 border"
                        style="width: 56px; height: 56px; background: #EAF7EF; color: #2AA14A;">
                        <i class="la la-file-alt" style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="mb-0 text-truncate" style="font-weight: 700; letter-spacing: -0.02em; color: #0f172a; font-size: 1.25rem;">
                            {{ $entryTitle }}
                        </h3>
                        <div class="text-muted small mt-1">
                            {{ $entrySubtitle }} · ID {{ $entry->getKey() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="card mb-3">
            <div class="card-header py-3 px-4">
                Chi tiết
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0 bp-show-table">
                    <tbody>
                        @foreach ($crud->columns() as $column)
                            <tr>
                                <th scope="row" class="pl-4 text-muted" style="width: 34%; font-weight: 500;">
                                    {!! $column['label'] !!}
                                </th>
                                <td class="pr-4">
                                    @if (! isset($column['type']))
                                        @include('crud::columns.text')
                                    @elseif (view()->exists('vendor.backpack.crud.columns.'.$column['type']))
                                        @include('vendor.backpack.crud.columns.'.$column['type'])
                                    @elseif (view()->exists('crud::columns.'.$column['type']))
                                        @include('crud::columns.'.$column['type'])
                                    @else
                                        @include('crud::columns.text')
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if ($crud->buttons()->where('stack', 'line')->count())
                            <tr>
                                <th scope="row" class="pl-4 text-muted pb-3" style="font-weight: 500;">
                                    {{ trans('backpack::crud.actions') }}
                                </th>
                                <td class="pr-4 pb-3">
                                    @include('crud::inc.button_stack', ['stack' => 'line'])
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('after_styles')
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
<style>
    .bp-show-table tbody tr + tr {
        border-top: 1px solid #f1f5f9;
    }
    .bp-show-table th,
    .bp-show-table td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        vertical-align: middle;
    }
    @media print {
        .d-print-none, .btn { display: none !important; }
    }
</style>
@endsection

@section('after_scripts')
<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
@endsection
