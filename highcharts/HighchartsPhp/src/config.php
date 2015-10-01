<?php

/**
 * Paths and names for the javascript libraries needed by higcharts/highstock charts
 */
$jsFiles = array(
    'jQuery' => array(
        'name' => 'jquery-2.1.3.min.js',
        //'path' => '//code.jquery.com/'
        'path' => '/highcharts/js/'
    ),

    'mootools' => array(
        'name' => 'mootools-yui-compressed.js',
        //'path' => '//ajax.googleapis.com/ajax/libs/mootools/1.4.5/'
        'path' => '/highcharts/js/'
    ),

    'prototype' => array(
        'name' => 'prototype.js',
        //'path' => '//ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/'
        'path' => '/highcharts/js/'
    ),

    'highcharts' => array(
        'name' => 'highcharts.js',
        //'path' => '//code.highcharts.com/'
        'path' => '/highcharts/js/'
    ),

    'highchartsMootoolsAdapter' => array(
        'name' => 'mootools-adapter.js',
        //'path' => '//code.highcharts.com/adapters/'
        'path' => '/highcharts/js/'
    ),

    'highchartsPrototypeAdapter' => array(
        'name' => 'prototype-adapter.js',
        'path' => '//code.highcharts.com/adapters/'
    ),

    'highstock' => array(
        'name' => 'highstock.js',
        //'path' => '//code.highcharts.com/stock/'
        'path' => '/highcharts/js/'
    ),

    'highstockMootoolsAdapter' => array(
        'name' => 'mootools-adapter.js',
        //'path' => '//code.highcharts.com/stock/adapters/'
        'path' => '/highcharts/js/'
    ),

    'highstockPrototypeAdapter' => array(
        'name' => 'prototype-adapter.js',
        //'path' => '//code.highcharts.com/stock/adapters/'
        'path' => '/highcharts/js/'
    ),

    //Extra scripts used by Highcharts 3.0 charts
    'extra' => array(
        'highcharts-more' => array(
            'name' => 'highcharts-more.js',
            //'path' => '//code.highcharts.com/'
            'path' => '/highcharts/js/'
        ),
        'exporting' => array(
            'name' => 'exporting.js',
            //'path' => '//code.highcharts.com/modules/'
            'path' => '/highcharts/js/'
        ),
    )
);
