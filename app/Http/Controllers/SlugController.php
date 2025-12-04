<?php

namespace App\Http\Controllers;

use App\Models\Slug;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;

class SlugController extends Controller
{
    /**
     * @throws BindingResolutionException
     */
    public function make($slug): bool|View
    {
        if ($slug == config('backpack.base.route_prefix')) {
            return false;
        }
        $dataSlug = Slug::findBySlug($slug);

        if ($dataSlug) {
            $app = app();
            $controller = $app->make($dataSlug->controller);

            return $controller->callAction($dataSlug->method, [$dataSlug->sluggable_id, $slug]);
        }

        abort(404);
    }
}
