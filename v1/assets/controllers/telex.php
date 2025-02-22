<?php
namespace telex;

use telegramBot\telegramBot;

class telex
{
    // Message from Telex
    public static function telexUpdate($data)
    {
        // Decode data into a PHP object
        $update = json_decode($data);

        // Extract message
        $message = strip_tags($update->message);

        // Extract group_id and user_id from settings
        $group_id = null;
        $user_id = null;

        foreach ($update->settings as $setting) {
            if ($setting->label === "Group ID") {
                $group_id = $setting->default;
            } elseif ($setting->label === "User ID") {
                $user_id = $setting->default;
            }
        }

        // Validate at least one ID is present
        if ($group_id === null && $user_id === null) {
            error_log("No valid Group ID or User ID found in Telex data.");
            return;
        }

        // Forward message based on configuration
        if (!empty($group_id)) {
            telegramBot::sendMessage($group_id, $message); // Send to group
            error_log("Message sent to Group ID: " . $group_id);
        }

        if (!empty($user_id)) {
            telegramBot::sendMessage($user_id, $message);  // Send to user
            error_log("Message sent to User ID: " . $user_id);
        }
    }
    // End of method 
}