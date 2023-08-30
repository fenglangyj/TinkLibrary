<?php

namespace tink\library\service;

use TelegramBot\Api\InvalidArgumentException;

/**
 *  TelegramBotApi SDK：https://github.com/TelegramBot/Api
 *  TelegramBotApi 文档：https://github.com/TelegramBot/Api
 */
class TelegramBotService
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
     * @return TelegramBotService
     */
    public static function instance(string $BOT_API_TOKEN): TelegramBotService
    {
        $key = $BOT_API_TOKEN;
        if (empty(self::$instance[$key])) {
            self::$instance[$key] = new static($BOT_API_TOKEN);
        }
        return self::$instance[$key];
    }

    /**
     * 返回服务参数
     * @return array
     */
    public function info(): array
    {
        return ['BOT_API_TOKEN' => $this->BOT_API_TOKEN];
    }

    /**
     * 发送普通消息
     * @return float|int
     */
    public function sendMessage(int $chatId, string $messageText)
    {
        try {
            $bot = new \TelegramBot\Api\BotApi($this->BOT_API_TOKEN);
            $ret = $bot->sendMessage($chatId, $messageText);
            $Chat = $ret->getChat();
            return $ret->getMessageId();
        } catch (InvalidArgumentException|\TelegramBot\Api\Exception|\Exception $e) {
            $errMsg = $e->getMessage() . PHP_EOL . $e->getFile() . ' ' . $e->getLine();
            self::log($errMsg, 'error');
            return 0;
        }
    }

    /**
     * 发送 ReplyKeyboardMarkup 消息
     * @param int $chatId
     * @param string $messageText
     * @return float|int
     */
    public function sendReplyKeyboardMarkup(int $chatId, string $messageText)
    {
        try {
            $bot = new \TelegramBot\Api\BotApi($this->BOT_API_TOKEN);
            $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(array(
                [
                    //不能用于频道，筛选
                    ['text' => 'text1', 'switch_inline_query' => 'switch_inline_query1']
                ],
                [
                    ['text' => 'text2', 'callback_data' => 'callback_data_bt2']
                ]
//                'is_persistent' => true,//用于创建带有预定义回复选项的自定义键盘，供用户与机器人进行交互。ReplyKeyboardMarkup类的可用属性包括keyboard、resize_keyboard、one_time_keyboard和selective。
//                'resize_keyboard' => true,//resize_keyboard（可调整大小）：设置为True时，键盘会自动调整大小以适应不同屏幕大小。默认为False。
//                'one_time_keyboard' => true,//one_time_keyboard（一次性键盘）：设置为True时，键盘会在用户选择一个按钮后自动关闭。默认为False。
//                'selective' => false,//selective（选择性）：设置为True时，键盘只对特定用户生效。默认为False，对所有用户生效。
//                'input_field_placeholder' => 'true',//在ReplyKeyboardMarkup的回复键盘中，您可以为其中一个按钮设置一个输入字段，以便用户可以在键盘上直接输入文本而不仅仅是选择按钮。为了显示这个输入字段，您需要在ReplyKeyboardMarkup中使用一个特殊的按钮类型，称为"keyboard button with request_contact"或"keyboard button with request_location"。当您使用这些特殊类型的按钮作为回复键盘中的一个按钮时，您可以通过设置按钮的input_field_placeholder属性来为输入字段设置占位文本。该占位文本将在输入字段为空时显示，以指示用户应该输入什么内容。
            ), true, true, true, true, 'input_field_placeholder');
            $response = $bot->sendMessage($chatId, $messageText, null, false, null, $keyboard);
            $Chat = $response->getChat();
            return $response->getMessageId();
        } catch (InvalidArgumentException|\TelegramBot\Api\Exception|\Exception $e) {
            $errMsg = $e->getMessage() . PHP_EOL . $e->getFile() . ' ' . $e->getLine();
            self::log($errMsg, 'error');
            return 0;
        }
    }

    /**
     * 发送 InlineKeyboardMarkup 消息
     * @param int $chatId
     * @param string $messageText
     * @param string $text
     * @return float|int
     */
    public function sendInlineKeyboardMarkup(int $chatId, string $messageText, string $text)
    {
        try {
            $bot = new \TelegramBot\Api\BotApi($this->BOT_API_TOKEN);
            $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup(
                [
                    /*[
                        //不能用于频道，将机器人分析给别人
                        ['text' => 'link1', 'switch_inline_query'=>'switch_inline_query1']
                    ],*/
                    [
                        ['text' => $text, 'callback_data' => $text],
                        /*['text' => 'Obter bônus de recém-chegado1', 'callback_data' => 'bt1'],
                        ['text' => 'Obter bônus de recém-chegado2', 'callback_data' => 'bt2']*/
                    ],
                    /*[
                        ['text' => 'Obter bônus de recém-chegado3', 'callback_data' => 'bt3'],
                        ['text' => 'Obter bônus de recém-chegado4', 'callback_data' => 'bt4']
                    ]*/
                ]
            );
            $response = $bot->sendMessage($chatId, $messageText, null, false, null, $keyboard);
            $Chat = $response->getChat();
            self::log(var_export($Chat, true), 'sendInlineKeyboardMarkup');
            return $response->getMessageId();
        } catch (InvalidArgumentException|\TelegramBot\Api\Exception|\Exception $e) {
            $errMsg = $e->getMessage() . PHP_EOL . $e->getFile() . ' ' . $e->getLine();
            self::log($errMsg, 'error');
            return 0;
        }
    }

    /**
     * 获取最新消息
     * @return \TelegramBot\Api\Types\Update[]
     */
    public function getUpdates(): array
    {
        try {
            $bot = new \TelegramBot\Api\BotApi($this->BOT_API_TOKEN);
            return $bot->getUpdates();
        } catch (\Exception $e) {
            $errMsg = $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine();
            self::log($errMsg, 'error');
        }
        return [];
    }

    /**
     * 设置Webhook
     * @param string $bot_web_hook_url
     * @return bool
     */
    public function setWebhook(string $bot_web_hook_url): bool
    {
        try {
            $bot = new \TelegramBot\Api\BotApi($this->BOT_API_TOKEN);
            $response = $bot->setWebhook($bot_web_hook_url);
            self::log(var_export($response, true));
            return true;
        } catch (\TelegramBot\Api\Exception|\Exception $e) {
            $errMsg = $e->getMessage() . PHP_EOL . $e->getFile() . ' ' . $e->getLine();
            self::log($errMsg, 'error');
            return false;
        }
    }

    /**
     * 写日志
     * @return void
     */
    public static function log($msg, $level = 'debug')
    {
        if (php_sapi_name() === 'cli') {
            // 在CLI模式下运行的代码
            $datetime = date('Y-m-d H:i:s');
            echo "[$datetime][$level] " . var_export($msg, true) . PHP_EOL;
        }
        /*else {
            // 在其他模式下运行的代码
            Log::log('BotService_' . $level, $msg);
        }*/
    }
}
