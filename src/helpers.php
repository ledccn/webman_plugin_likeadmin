<?php

use David\LikeAdmin\LoginMiddleware;

/**
 * 当前登录用户id
 * @return integer|null
 */
function like_user_id(): ?int
{
    $user = request()->userInfo ?? null;
    return $user ? ($user['id'] ?? null) : session('user.id');
}

/**
 * 当前登录用户
 * @param array|string|null $fields
 * @return array|mixed|null
 */
function like_user(array|string $fields = null): mixed
{
    $user = request()->userInfo ?? null;
    if (!$user) {
        LoginMiddleware::refreshUserSession();
        if (!$user = session('user')) {
            return null;
        }
    }
    if ($fields === null) {
        return $user;
    }
    if (is_array($fields)) {
        $results = [];
        foreach ($fields as $field) {
            $results[$field] = $user[$field] ?? null;
        }
        return $results;
    }
    return $user[$fields] ?? null;
}
