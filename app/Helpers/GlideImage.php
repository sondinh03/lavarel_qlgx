<?php

namespace App\Helpers;

use League\Glide\Urls\UrlBuilderFactory;

class GlideImage
{
    public static function resize($path, $options = []): string
    {
        $sign_key = env('GLIDE_KEY', false);

        $urlBuilder = UrlBuilderFactory::create('/cache/', $sign_key);

        return $urlBuilder->getUrl($path, $options);
    }
}
