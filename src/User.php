<?php

namespace David\LikeAdmin;

use support\Model;

/**
 * @property integer $id 主键(主键)
 * @property integer $sn 编号
 * @property string $avatar 头像
 * @property string $real_name 真实姓名
 * @property string $nickname 用户昵称
 * @property string $account 用户账号
 * @property string $password 用户密码
 * @property string $mobile 用户电话
 * @property integer $sex 用户性别: [1=男, 2=女]
 * @property integer $channel 注册渠道: [1-微信小程序 2-微信公众号 3-手机H5 4-电脑PC 5-苹果APP 6-安卓APP]
 * @property integer $is_disable 是否禁用: [0=否, 1=是]
 * @property string $login_ip 最后登录IP
 * @property integer $login_time 最后登录时间
 * @property integer $is_new_user 是否是新注册用户: [1-是, 0-否]
 * @property string $user_money 用户余额
 * @property string $total_recharge_amount 累计充值
 * @property integer $create_time 创建时间
 * @property integer $update_time 更新时间
 * @property integer $delete_time 删除时间
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'la_user';

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
}
