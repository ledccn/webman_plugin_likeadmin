<?php

use David\LikeAdmin\LoginMiddleware;

return [
    'like' => [
        LoginMiddleware::class,
    ],
    'gateway' => [
        LoginMiddleware::class,
    ],
];