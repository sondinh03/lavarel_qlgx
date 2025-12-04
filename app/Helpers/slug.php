<?php

if (! function_exists('slug')) {
    /**
     * @param $collect
     * @return array|mixed
     */
    function slug($collect): mixed
    {
        return data_get(data_get($collect, 'slug'), 'keyword');
    }
}
