<?php

use tink\library\service\TelegramBotService;

include_once dirname(__DIR__) . '/vendor/autoload.php';
//include_once dirname(__DIR__) . '/vendor/topthink/framework/src/helper.php';

$TelegramBotServiceObj = TelegramBotService::instance('BOT_API_TOKEN');
print_r($TelegramBotServiceObj->info());

# 获取消息记录
$ls = $TelegramBotServiceObj->getUpdates();
print_r($ls);

# 发送普通消息
$ls = $TelegramBotServiceObj->sendMessage(34234234234,'消息内容');
print_r($ls);