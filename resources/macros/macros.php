<?php

use Illuminate\Support\Facades\Request;

Html::macro('linkWithIcon', function ($url, $text, $attributes = [], $icons = [])
{
    $before = '';
    $after = '';
    $attribute = '';

    foreach ($attributes as $key => $value)
    {
        $attribute .= $key . '="' . $value . '"';
    }

    foreach ($icons as $key => $icon)
    {
        if ($key === 'before')
        {
            $before .= '<i class="' . $icon . '"></i>';
        }
        else
        {
            $after = '<i class="' . $icon . '"></i>';
        }
    }
    return '<a href="' . $url . '" ' . $attribute . '>' . $before . ' <span>' . $text . '</span> ' . $after . '</a>';
});

Html::macro('active', function ($route)
{
    return Request::route()->getName() === $route ? 'active' : '';
});

Html::macro('collapse', function ($routes)
{
    foreach ($routes as $route)
    {
        if (Request::route()->getName() === $route)
        {
            return '';
        }
    }
    
    return 'collapsed-box';
});

Html::macro('setIconByRoute', function ($routes, $icons)
{
    foreach ($routes as $route)
    {
        if (Request::route()->getName() === $route)
        {
            return $icons[0];
        }
    }
    
    return $icons[1];
});