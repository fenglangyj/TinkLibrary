<?php

namespace tink\library\service\TinkGame;

/**
 * tinkGame公共服务
 */
class CommonService
{
    /**
     * 单例对象列表
     * @var mixed
     */
    private static $instance;

    /**
     * FJ机器人 token
     * @var string
     */
    protected string $BOT_API_TOKEN = '';

    /**
     * 构造函数
     * @param string|null $BOT_API_TOKEN 机器人token
     */
    public function __construct(string $BOT_API_TOKEN)
    {
        $this->BOT_API_TOKEN = $BOT_API_TOKEN;
    }

    /**
     * 实例化单例
     * @param string|null $BOT_API_TOKEN
     * @return \tink\library\service\TinkGame\CommonService
     */
    public static function instance(string $BOT_API_TOKEN): CommonService
    {
        $key = $BOT_API_TOKEN;
        if (empty(self::$instance[$key])) {
            self::$instance[$key] = new static($BOT_API_TOKEN);
        }
        return self::$instance[$key];
    }

    /**
     * 更新可提现余额
     * @param int $user_id 用户id
     * @param int $data_type 动账类型
     * @param int $amount 动账金额
     * @param int $balance 动账后余额
     * @param \Redis $redis
     * @return string
     */
    public function changeWithdrawPocket(int $user_id, int $data_type, int $amount, int $balance, \Redis &$redis): string
    {
        if ($data_type != 1 or $balance < 0 or $amount <= 0) {
            return '';
        }
        $UserPacketsKey = "App\\Model\\User\\UserPacketsModel:detail:" . $user_id;
        $ret_amount = $balance / 100;
        //累计充值 - 累计提现
        $recharge_count = $redis->hGet($UserPacketsKey, 'recharge_count');
        $withdraw_count = $redis->hGet($UserPacketsKey, 'withdraw_count');
        $M = intval($recharge_count) - intval($withdraw_count) + 3000;
        if ($M > 0) {
            if ($ret_amount <= 200) {
                $withdraw_pocket_rate = 20;
            } elseif ($ret_amount < 500) {
                $withdraw_pocket_rate = 40;
            } elseif ($ret_amount < 2000) {
                $withdraw_pocket_rate = 60;
            } else {
                $withdraw_pocket_rate = 100;
            }
        } else {
            if ($ret_amount < 200) {
                $withdraw_pocket_rate = 100;
            } else {
                $withdraw_pocket_rate = 1000;
            }
        }
        $withdraw_pocket_inc = ceil(abs($amount) / $withdraw_pocket_rate);
        if ($withdraw_pocket_inc == 1) {//30%概率使用
            try {
                if (random_int(0, 100) < 30) {
                    $redis->hIncrBy($UserPacketsKey, 'withdraw_pocket', $withdraw_pocket_inc);
                    $amount_r = $amount / 100;
                    $withdraw_pocket_inc /= 100;
                    return "user_id:$user_id 动账金额:$amount_r 动账后余额:$ret_amount 充值合计:$recharge_count 提现合计:$withdraw_count M:$M 倍率:$withdraw_pocket_rate 打码金额：$withdraw_pocket_inc 元";
                }
                return '';
            } catch (\Exception $e) {
                return $e->getMessage() . PHP_EOL . $e->getFile() . ':' . $e->getLine();
            }
        } else {
            $redis->hIncrBy($UserPacketsKey, 'withdraw_pocket', $withdraw_pocket_inc);
            $amount_r = $amount / 100;
            $withdraw_pocket_inc /= 100;
            return "user_id:$user_id 动账金额:$amount_r 动账后余额:$ret_amount 充值合计:$recharge_count 提现合计:$withdraw_count M:$M 倍率:$withdraw_pocket_rate 打码金额：$withdraw_pocket_inc 元";
        }
    }

}
