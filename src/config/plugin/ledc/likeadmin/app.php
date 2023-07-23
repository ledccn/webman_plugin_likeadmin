<?php
return [
    'enable' => true,
    //未登录固定返回码
    'invalid_token_code' => -1,
    'jwt' => [
        // 请求头内携带JWT的字段
        'header_field' => ['Authorization', 'token'],
        // 从请求头获取JWT后的回调函数
        'jwt_callback' => function (string $field, string $jwt) {
            return $jwt;
        },
        // 算法类型 HS256、HS384、HS512、RS256、RS384、RS512、ES256、ES384、Ed25519
        'algorithms' => 'HS256',
        // access令牌秘钥
        'access_secret_key' => '',
        // 数据在JWT里面的列名称
        'data_column_name' => 'user',
    ],
];
