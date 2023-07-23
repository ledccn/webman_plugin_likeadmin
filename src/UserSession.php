<?php

namespace David\LikeAdmin;

use Illuminate\Database\Eloquent\Builder;
use support\Model;

/**
 * @property integer $id (主键)
 * @property integer $user_id 用户id
 * @property integer $terminal 客户端类型：1-微信小程序；2-微信公众号；3-手机H5；4-电脑PC；5-苹果APP；6-安卓APP
 * @property string $token 令牌
 * @property integer $update_time 更新时间
 * @property integer $expire_time 到期时间
 */
class UserSession extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'la_user_session';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 获取session
     * @param string $token
     * @return Builder|self|null
     */
    public static function getSessionByToken(string $token): self|Builder|null
    {
        return static::where('token', '=', $token)->first();
    }

    /**
     * 获取有效session
     * @param string $token
     * @return UserSession|Builder|null
     */
    public static function firstByToken(string $token): self|Builder|null
    {
        return static::where('token', '=', $token)->where('expire_time', '>', time())->first();
    }

    /**
     * 延长token过期时间
     * @param string $token
     * @return bool
     */
    public static function overtimeToken(string $token): bool
    {
        $time = time();
        $model = static::getSessionByToken($token);
        if (!$model) {
            return false;
        }
        $model->expire_time = $time + 3600 * 8;
        $model->update_time = $time;
        return $model->save();
    }

    /**
     * 设置token为过期
     * @param string $token
     * @return bool
     */
    public static function expireToken(string $token): bool
    {
        $model = static::getSessionByToken($token);
        if (!$model) {
            return false;
        }
        $time = time();
        $model->expire_time = $time;
        $model->update_time = $time;
        return $model->save();
    }
}
