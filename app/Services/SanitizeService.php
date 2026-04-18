<?php

namespace App\Services;

class SanitizeService
{
    public static function cleanHtml(?string $html): string
    {
        if (!$html) return '';

        $config = \HTMLPurifier_Config::createDefault();
        
        // Настройки под твой проект
        $config->set('HTML.SafeIframe', true); // Разрешить iframe (для видео)
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $config->set('HTML.Allowed', 'p,b,i,u,strong,em,a[href|title],ul,ol,li,br,img[src|alt|width|height],table,tr,td,th,tbody,thead,blockquote,h1,h2,h3');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }
}