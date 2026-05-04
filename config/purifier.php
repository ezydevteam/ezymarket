<?php

return [
    'encoding'           => 'UTF-8',
    'finalize'           => true,
    'ignoreNonStrings'   => false,
    'cachePath'          => storage_path('app/purifier'),
    'cacheFileMode'      => 0755,
    'settings'      => [
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],
        'rich_text' => [
            'HTML.DefinitionID' => 'rich-text',
            'HTML.DefinitionRev' => 1,
            'HTML.Allowed' => 'p[class|style],br,strong,b,em,i,u,h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style],ul[class|style],ol[class|style],li[class|style],blockquote[class|style],a[href|title|target|class|rel],img[src|alt|width|height|class|style],table[class|style],tr[class|style],td[class|style],th[class|style],thead[class|style],tbody[class|style],div[class|style],span[class|style],pre,code',
            'Attr.AllowedClasses' => 'editor-code-perforated,editor-card-alert,editor-card-primary,editor-card-danger,editor-card-info,perforated-text',
            'CSS.AllowedProperties' => 'color,background-color,border,padding,margin,text-align,width,height',
            'HTML.ForbiddenElements' => 'script,style,iframe,object,embed,form,input,button,meta,link,base',
            'Attr.AllowedFrameTargets' => '_blank',
            'HTML.Nofollow' => true,
            'Attr.AllowedRel' => 'noopener,noreferrer',
            'HTML.TargetBlank' => true,
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.RemoveSpansWithoutAttributes' => false,
            'URI.DisableExternalResources' => false,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'data' => true],
            'Attr.EnableID' => false,
            'HTML.SafeIframe' => false,
            'Output.TidyFormat' => true,
        ],
    ],
];
