<?php /**
 * Created by PhpStorm.
 * User: 小灰灰
 * Date: 2024-06-06
 * Time: 15:21:57
 * Info:
 */

namespace process;

use app\Request;
use Webman\Push\Api;
use Workerman\Timer;

class Status {

    public function __construct() {
        Timer::add(3, function () {
            $this->status();
        });
    }

    public function status() {
        static $user_count = 0, $page_count = 0;
        $api = new Api('http://127.0.0.1:3232', config('plugin.webman.push.app.app_key'),
            config('plugin.webman.push.app.app_secret'));
        $result = $api->get('/channels', ['filter_by_prefix' => 'user', 'info' => 'subscription_count']);
        if (!$result || $result['status'] != 200) {
            return;
        }
        $channels = $result['result']['channels'];
        $user_count_now = count($channels);
        $page_count_now = 0;
        foreach ($channels as $channel) {
            $page_count_now += $channel['subscription_count'];
        }
        if ($page_count_now == $page_count && $user_count_now === $user_count) {
            return;
        }
        $user_count = $user_count_now;
        $page_count = $page_count_now;

        $api->trigger('online-page', 'update_online_status',
            "当前<b>$user_count</b>人在线，共打开<b>$page_count</b>个页面");
    }
}
