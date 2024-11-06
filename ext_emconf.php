<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'pwa_manifest',
    'description' => 'This extension provides Web App Manifest configuration.',
    'state' => 'beta',
    'author' => 'Łukasz Uznański',
    'author_email' => 'extensions@macopedia.pl',
    'category' => 'fe',
    'internal' => '',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
