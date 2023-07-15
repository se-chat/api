<?php

/**
 * Copyright (c) Vincent Klaiber.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/vinkla/laravel-hashids
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Hashids Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like.
    |
    */

    'connections' => [

        'main' => [
            'salt' => '',
            'length' => 0,
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],
        'member' => [
            'salt' => 'member',
            'length' => 4,
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],
        'group' => [
            'salt' => 'group',
            'length' => 4,
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],
        'invitation-notice' => [
            'salt' => 'invitation-notice',
            'length' => 4,
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],
        'contact'=> [
            'salt' => 'contact',
            'length' => 4,
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],
        'message'=> [
            'salt' => 'message',
            'length' => 4,
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],
        'alternative' => [
            'salt' => 'your-salt-string',
            'length' => 'your-length-integer',
            'alphabet' => env('HASH_ID_ALPHABET','abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
        ],

    ],

];
