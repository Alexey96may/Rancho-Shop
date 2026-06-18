<?php

namespace App\Services;

use HTMLPurifier_Config;
use HTMLPurifier;

class SanitizeService
{
    public static function cleanHtml(?string $html): string
    {
        if (!$html) return '';

        $config = HTMLPurifier_Config::createDefault();
        
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $config->set('HTML.Allowed', 'p[class|style],span[class|style],b[class|style],i[class|style],u[class|style],strong[class|style],em[class|style],a[href|title|class|style],ul[class],ol[class|style],li[class|style],br,img[src|alt|width|height|class],table[class],tr[class|style],td[class|style],th[class|style],tbody,thead,blockquote[class|style],h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style]');
        $config->set('Attr.AllowedClasses', null);
        $config->set('CSS.AllowedProperties', 'color,background-color,text-align');

        $config->set('CSS.Trusted', true);
        
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
}