<?php

namespace David\LikeAdmin;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ReflectionClass;
use ReflectionException;
use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;

/**
 * Like用户登录中间件
 */
class LoginMiddleware implements MiddlewareInterface
{
    /**
     * 路由向中间件传参：是否必须登录的键名
     * - true不需要登录，false需要登录
     */
    const noNeedLogin = 'noNeedLogin';

    /**
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        //凭token初始化session
        $token = $request->header('token', $request->cookie('token'));
        if ($token && ctype_alnum($token) && strlen($token) <= 40) {
            if (!isset($request->sid)) {
                $request->sid = $token;
            }
            if (!like_user_id()) {
                self::setUserInfo($token);
            }
        } else {
            static::jwtDecodeFromHeaders($request);
        }

        $code = 0;
        $msg = '';
        if (!self::canAccess($request, $code, $msg)) {
            $response = json(['code' => $code, 'msg' => $msg, 'type' => 'error']);
        } else {
            $response = $request->method() === 'OPTIONS' ? response('') : $handler($request);
        }

        return $response;
    }

    /**
     * 解析JWT请求
     * @param Request $request
     * @return void
     */
    public static function jwtDecodeFromHeaders(Request $request): void
    {
        $config = config('plugin.ledc.likeadmin.app.jwt');
        $jwt_callback = $config['jwt_callback'] ?? null;
        foreach ($config['header_field'] as $field) {
            $authorization = $request->header($field);
            if ($authorization && 2 === substr_count($authorization, '.')) {
                try {
                    $jwt = $jwt_callback instanceof Closure ? $jwt_callback($field, $authorization) : $authorization;
                    $decoded = JWT::decode($jwt, new Key($config['access_secret_key'], $config['algorithms']));
                    $payload = json_decode(json_encode($decoded), true);
                    $data_column_name = $config['data_column_name'];
                    if (!empty($payload[$data_column_name])) {
                        $request->userInfo = $payload[$data_column_name];
                    }
                } catch (Throwable $throwable) {
                    $request->jwtException = $throwable->getMessage();
                }

                return;
            }
        }
    }

    /**
     * 判断是否有权限
     * @param Request $request
     * @param int $code
     * @param string $msg
     * @return bool
     */
    protected static function canAccess(Request $request, int &$code = 0, string &$msg = ''): bool
    {
        $controller = $request->controller;
        $action = $request->action;
        // 无控制器信息说明是函数调用，函数不属于任何控制器，鉴权操作应该在函数内部完成。
        if (!$controller) {
            // 默认路由 $request->route为null，所以需要判断 $request->route 是否为空
            $route = $request->route;
            if (!$route) {
                return true;
            }
            //路由是否需要验证登录
            if ($route->param(self::noNeedLogin)) {
                //路由不需要登录
                return true;
            } else {
                //默认需要验证登录
                goto verify_user;
            }
        }

        // 获取控制器鉴权信息
        try {
            $class = new ReflectionClass($controller);
        } catch (ReflectionException $e) {
            $msg = '控制器不存在';
            $code = 404;
            return false;
        } catch (Throwable $throwable) {
            $msg = '控制器不存在';
            $code = 500;
            return false;
        }
        $properties = $class->getDefaultProperties();
        $noNeedLogin = $properties['noNeedLogin'] ?? [];
        // 不需要登录
        if (in_array($action, $noNeedLogin)) {
            return true;
        }

        verify_user:
        //获取登录信息
        $user = like_user();
        if (!$user) {
            $msg = $request->jwtException ?? '请登录';
            // 未登录固定返回码
            $code = config('plugin.ledc.likeadmin.app.invalid_token_code', -1);
            return false;
        }
        return true;
    }

    /**
     * 刷新当前用户session
     * @param bool $force 强制刷新
     * @return void
     */
    final public static function refreshUserSession(bool $force = false): void
    {
        if (!like_user_id()) {
            return;
        }
        $time_now = time();
        // session在3600秒内不刷新
        $session_ttl = 3600;
        $session_last_update_time = session('user.session_last_update_time', 0);
        if (!$force && $time_now - $session_last_update_time < $session_ttl) {
            return;
        }

        $session = request()->session();
        if (!UserSession::overtimeToken($session->getId())) {
            $session->forget('user');
            return;
        }

        self::setUserInfo($session->getId());
    }

    /**
     * 通过有效token设置用户信息缓存
     * @param string $token
     * @return void
     */
    final protected static function setUserInfo(string $token): void
    {
        $userSession = UserSession::firstByToken($token);
        if ($userSession instanceof UserSession) {
            $session = request()->session();
            $user = User::find($userSession->user_id);
            if (!$user) {
                $session->forget('user');
                return;
            }

            $userInfo = [
                'id' => $user->id,
                'user_id' => $user->id,
                'nickname' => $user->nickname,
                'token' => $token,
                'sn' => $user->sn,
                'mobile' => $user->mobile,
                'avatar' => $user->avatar,
                'terminal' => $userSession->terminal,
                'expire_time' => $userSession->expire_time,
                'session_last_update_time' => time(),
            ];
            $session->set('user', $userInfo);
        }
    }
}
