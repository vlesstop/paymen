<?php

namespace App\Http\Controllers\V1\Guest;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User; // 引入用户模型
use App\Models\Plan; // 引入套餐模型
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\TelegramService;
use App\Services\AuthService; // 引入AuthService
use App\Jobs\SendEmailJob; // 引入邮件发送任务
use App\Utils\Helper; // 引入 Helper
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // 引入邮件模块

class PaymentController extends Controller
{
    public function notify($method, $uuid, Request $request)
    {
        try {
            $paymentService = new PaymentService($method, null, $uuid);
            $verify = $paymentService->notify($request->input());
            if (!$verify) abort(500, 'verify error');
            if (!$this->handle($verify['trade_no'], $verify['callback_no'])) {
                abort(500, 'handle error');
            }
            return(isset($verify['custom_result']) ? $verify['custom_result'] : 'success');
        } catch (\Exception $e) {
            abort(500, 'fail');
        }
    }

    private function handle($tradeNo, $callbackNo)
    {
        $order = Order::where('trade_no', $tradeNo)->first();
        if (!$order) {
            abort(500, 'order is not found');
        }
        if ($order->status !== 0) return true;
        $orderService = new OrderService($order);
        if (!$orderService->paid($callbackNo)) {
            return false;
        }
        $telegramService = new TelegramService();
        $message = sprintf(
            "💰成功收款%s元\n———————————————\n订单号：%s",
            $order->total_amount / 100,
            $order->trade_no
        );
        $telegramService->sendMessageWithAdmin($message);

        // 从订单对象中获取到用户ID
        $userid = $order->user_id;
        // 通过用户ID从数据库获取用户信息对象
        $user = User::find($userid);
        // 创建一个AuthService实例，传入用户信息对象
        $authService = new AuthService($user);
        // 调用 generateAuthData 方法生成 authData
        $request = new Request();
        $userData = $authService->generateAuthData($request);
        // 从生成的 authData 中获取 token 和 auth_data
        $token = $userData['token'];
        $authData = $userData['auth_data'];
        // 创建变量 $url = 站点+/#/login?auth_data=auth_data
        $loginLink = config('v2board.app_url') . '/#/login?auth_data=' . $authData;
        // 获取订阅链接、套餐名称、套餐周期
        $subscribeUrl = Helper::getSubscribeUrl($user['token']);
        $plan = Plan::find($user->plan_id);
        $subscribePlan = $plan->name;
        switch ($order->period) {
            case 'month_price':
                $subscribePeriod = '月付';
                break;
            case 'quarter_price':
                $subscribePeriod = '季付';
                break;
            case 'half_year_price':
                $subscribePeriod = '半年付';
                break;
            case 'year_price':
                $subscribePeriod = '年付';
                break;
            case 'two_year_price':
                $subscribePeriod = '两年付';
                break;
            case 'three_year_price':
                $subscribePeriod = '三年付';
                break;
            case 'onetime_price':
                $subscribePeriod = '一次性';
                break;
            default:
                $subscribePeriod = '未知周期';
        }

        // 定义 log 对象
        $log = [
            'order' => $order,
            'user' => $user,
            'plan' => $plan,
            'authService' => $authService,
            'user_id' => $userid,
            'user_email' => $user->email,
            'userData' => $userData,
            'token' => $token,
            'authData' => $authData,
            'loginLink' => $loginLink,
            'subscribeUrl' => $subscribeUrl,
            'subscribePlan' => $subscribePlan,
            'subscribePeriod' => $subscribePeriod,
        ];
        error_log('查看日志对象 ===> ' . print_r($log, true), 3, '/tmp/send_email.log');

        // 调用SendEmailJob::dispatch发送邮件，模版选 pay，传入用户邮箱、主题、模版名、模版值
        SendEmailJob::dispatch([
            'email' => $user->email,
            'subject' => config('v2board.app_name', 'V2Board') . ' - 订单支付成功',
            'template_name' => 'pay',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'url' => config('v2board.app_url'),
                'loginLink' => $loginLink,
                'subscribeUrl' => $subscribeUrl,
                'subscribePlan' => $subscribePlan,
                'subscribePeriod' => $subscribePeriod,
            ]
        ]);

        return true;
    }
}
