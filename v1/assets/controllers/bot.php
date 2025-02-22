<?php
namespace telegramBot;

use cUtils\cUtils;

class telegramBot
{
    
    // Incoming webhook updates from Telegram
    public static function handleRequest($update)
    {

        // Check if the bot is added to a group
        if (isset($update->message->new_chat_members)) {
            foreach ($update->message->new_chat_members as $new_member) {
                cUtils::loadEnv(__DIR__ . '/.env');
                if ($new_member->is_bot && $new_member->id == getenv('TELEGRAM_BOT_ID')) {
                    $chat_id = $update->message->chat->id;

                    // Send the "Get Group ID" menu
                    self::sendGroupIdMenu($chat_id);
                    return;
                }
            }
        }

        // New Message
        if (isset($update->message)) {
            $chat_id = $update->message->chat->id;
            $message_text = $update->message->text ?? '';

            // Ignore /start command in groups
            if ($chat_id < 0 && $message_text == "/start") {
                return;
            }
            self::menu($update);
        }
        
        // Callback Queries
        if (isset($update->callback_query)) {
            self::callbackHandler($update->callback_query);
        }
    }
    // End of method


    // Return menu
    public static function menu($update)
    {
        // Extract ID
        $chatId = $update->message->chat->id;

        // Define the keyboard
        $keyboard = [
            "inline_keyboard" => [
                [
                    ['text' => '📢 Group', 'callback_data' => 'setup_group'],
                    ['text' => '📩 Private Chat', 'callback_data' => 'setup_private']
                ],
                [
                    ['text' => '🔄 Both (Group & Private Chat)', 'callback_data' => 'setup_both']
                ],
                [
                    ['text' => 'ℹ️ About', 'callback_data' => 'about'],
                    ['text' => '🆘 Help', 'callback_data' => 'help']
                ]
            ]
        ];

        $data = [
            'chat_id' => $chatId,
            'text' => "Where should I send alerts?",
            'reply_markup' => json_encode($keyboard)
        ];

        self::sendMessage($chatId, "Hello! I help forward alerts from Telex to Telegram."); // message before the keyboard
        cUtils::loadEnv(__DIR__ . '/.env');
        cUtils::callTelegramAPI("https://api.telegram.org/bot" . getenv('TELEGRAM_BOT_TOKEN') . "/sendMessage", $data);
    }
    // End of method


    // Send a text message
    public static function sendMessage($chatId, $message)
    {
        cUtils::loadEnv(__DIR__ . '/.env');
        $url = "https://api.telegram.org/bot" . getenv('TELEGRAM_BOT_TOKEN') . "/sendMessage";
        $data = ['chat_id' => $chatId, 'text' => $message];

        cUtils::callTelegramAPI($url, $data);
    }
    // End of method


    // Handle all callback queries
    public static function callbackHandler($callback_query)
    {
        if (!isset($callback_query->message) || !isset($callback_query->message->chat)) {
            error_log("Invalid callback_query structure: message or chat is missing.");
            return;
        }

        $callback_data = $callback_query->data;
        $callback_chat_id = $callback_query->message->chat->id;

        switch ($callback_data) {
            case 'setup_group':
                self::setupGroup($callback_query);
                break;
            case 'setup_private':
                self::setupPrivate($callback_query);
                break;
            case 'setup_both':
                self::setupBoth($callback_query);
                break;
            case 'about':
                self::about($callback_query);
                break;
            case 'help':
                self::help($callback_query);
                break;
            case 'get_group_id':
                self::sendGroupId($callback_chat_id);
                break;
            default:
                error_log("Unknown callback_data: " . $callback_data);
                break;
        }
    }
    // End of method


    // Callback Buttons
    // Group Setup
    public static function setupGroup($callback_query)
    {
        $callback_chat_id = $callback_query->message->chat->id;
        $message = "1️⃣ Add me - @TelexAlert_bot to your Telegram group.
                        \n\n2️⃣ Click the button to get your Group ID.";

        self::sendMessage($callback_chat_id, $message);
    }

    // Private Chat Setup
    public static function setupPrivate($callback_query)
    {
        $callback_chat_id = $callback_query->message->chat->id;
        $message = "You selected Private Chat. Alerts will be sent to your private chat.
                    \n\n ✅ Your User ID is: `$callback_chat_id`
                    \n NOTE: Add your USER ID to the settings in Telex";

        self::sendMessage($callback_chat_id, $message);
    }

    // Both Group and Private Chat Setup
    public static function setupBoth($callback_query)
    {
        $callback_chat_id = $callback_query->message->chat->id;
        $message = "You selected Both Group and Private Chat. Alerts will be sent to both.
                    \n\n ✅ Your User ID is: `$callback_chat_id`
                    \n NOTE: Add your USER ID to the settings in Telex 
                    \n After, add me - @TelexAlert_bot to your group and to get its ID.";

        self::sendMessage($callback_chat_id, $message);
    }

    // About
    public static function about($callback_query)
    {
        $callback_chat_id = $callback_query->message->chat->id;
        $message = "About TelexAlert Bot:\nThis bot helps forward alerts from Telex to Telegram.";

        self::sendMessage($callback_chat_id, $message);
    }

    // Help
    public static function help($callback_query)
    {
        $callback_chat_id = $callback_query->message->chat->id;
        $message = "Help:\nUse the buttons to configure where alerts should be sent.";

        self::sendMessage($callback_chat_id, $message);
    }

    // Send "Get Group ID" menu
    public static function sendGroupIdMenu($chat_id)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🔍 Get Group ID', 'callback_data' => 'get_group_id']
                ]
            ]
        ];

        $data = [
            'chat_id' => $chat_id,
            'text' => "✅ Thanks for adding me! Click the button below to get your Group ID.",
            'reply_markup' => json_encode($keyboard)
        ];

        cUtils::callTelegramAPI("https://api.telegram.org/bot" . getenv('TELEGRAM_BOT_TOKEN') . "/sendMessage", $data);
    }

    // Send Group ID
    public static function sendGroupId($callback_chat_id)
    {
        if ($callback_chat_id < 0) {  // Groups negative chat IDs
            $message = "✅ Your Group ID is: `" . $callback_chat_id . "`\n\n (Enter this in Telex to receive alerts.)";
        } else {
            $message = "⚠️ This command only works inside a group.";
        }

        self::sendMessage($callback_chat_id, $message);
    }
    // **************
    // End of callbacks
}