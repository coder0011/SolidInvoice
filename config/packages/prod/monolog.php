<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Config\MonologConfig;

return static function (MonologConfig $config): void {
    $config
        ->handler('main')
        ->type('fingers_crossed')
        ->actionLevel('error')
        ->handler('nested')
        ->bufferSize(50)
        ->excludedHttpCode(404)
        ->excludedHttpCode(405);

    $config
        ->handler('nested')
        ->type('stream')
        ->path('php://stderr')
        ->level('debug')
        ->formatter('monolog.formatter.json')
    ;

    $config
        ->handler('console')
        ->type('console')
        ->processPsr3Messages(false)
        ->channels()
        ->elements(['!event', '!doctrine']);

    /*
     @TODO: Only enable deprecation logging for specific scenarios
     $config
        ->handler('deprecation')
        ->type('stream')
        ->path('php://stderr')
        ->channels()
        ->elements(['deprecation']);*/
};
