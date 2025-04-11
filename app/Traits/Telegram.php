<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Telegram
{
    /**
     * Send a basic text message to Telegram
     *
     * @param string $chat_id Telegram chat ID
     * @param string $content Message content
     * @param string $parse_mode Text parsing mode (markdown or html)
     * @param bool $disable_web_page_preview Disable link previews
     * @param bool $disable_notification Send silently
     * @return array
     */
    public function sendTelegramMessage(
        string $chat_id,
        string $content,
        string $parse_mode = null,
        bool $disable_web_page_preview = false,
        bool $disable_notification = false
    ): array {
        $payload = [
            'chat_id' => $chat_id,
            'text' => $content,
            'disable_web_page_preview' => $disable_web_page_preview,
            'disable_notification' => $disable_notification,
        ];

        if ($parse_mode && in_array(strtolower($parse_mode), ['markdown', 'html'])) {
            $payload['parse_mode'] = $parse_mode;
        }

        return $this->sendTelegramRequest('sendMessage', $payload);
    }

    /**
     * Send an OTP message (wrapper for sendTelegramMessage with common OTP settings)
     *
     * @param string $chat_id Telegram chat ID
     * @param string $content OTP message content
     * @return array
     */
    public function sendTelegramOtp(string $chat_id, string $content): array
    {
        return $this->sendTelegramMessage(
            $chat_id,
            "Your OTP code: $content\n\nDo not share this with anyone.",
            'markdown',
            true
        );
    }

    /**
     * Send a photo to Telegram
     *
     * @param string $chat_id Telegram chat ID
     * @param string $photo_url URL or file_id of the photo
     * @param string|null $caption Photo caption
     * @param string|null $parse_mode Caption parsing mode
     * @param bool $disable_notification Send silently
     * @return array
     */
    public function sendTelegramPhoto(
        string $chat_id,
        string $photo_url,
        string $caption = null,
        string $parse_mode = null,
        bool $disable_notification = false
    ): array {
        $payload = [
            'chat_id' => $chat_id,
            'photo' => $photo_url,
            'disable_notification' => $disable_notification,
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }

        if ($parse_mode && in_array(strtolower($parse_mode), ['markdown', 'html'])) {
            $payload['parse_mode'] = $parse_mode;
        }

        return $this->sendTelegramRequest('sendPhoto', $payload);
    }

    public function sendTelegramAnimation(
        string $chat_id,
        string $animation_url,
        string $caption = null,
        string $parse_mode = null,
        bool $disable_notification = false,
        int $width = 100,
        int $height = 100
    ): array {
        $payload = [
            'chat_id' => $chat_id,
            'animation' => $animation_url,
            'disable_notification' => $disable_notification,
            'width' => $width,
            'height' => $height,
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }

        if ($parse_mode && in_array(strtolower($parse_mode), ['markdown', 'html'])) {
            $payload['parse_mode'] = $parse_mode;
        }

        return $this->sendTelegramRequest('sendAnimation', $payload);
    }

    /**
     * Send a markdown formatted message to Telegram
     *
     * @param string $chat_id Telegram chat ID
     * @param string $content Markdown formatted content
     * @param bool $disable_web_page_preview Disable link previews
     * @return array
     */
    public function sendTelegramMarkdown(
        string $chat_id,
        string $content,
        bool $disable_web_page_preview = true
    ): array {
        return $this->sendTelegramMessage(
            $chat_id,
            $content,
            'markdown',
            $disable_web_page_preview
        );
    }

    /**
     * Send a README/documentation style message
     *
     * @param string $chat_id Telegram chat ID
     * @param string $title Title of the README
     * @param string $content Content in markdown format
     * @return array
     */
    public function sendTelegramReadme(
        string $chat_id,
        string $title,
        string $content
    ): array {
        $formatted = "*$title*\n\n" . $content;
        return $this->sendTelegramMarkdown($chat_id, $formatted);
    }

    /**
     * Generic Telegram API request handler
     *
     * @param string $method Telegram API method
     * @param array $payload Request payload
     * @return array
     */
    protected function sendTelegramRequest(string $method, array $payload): array
    {
        $telegramApiUrl = $this->getTelegramApiUrl($method);

        try {
            $response = Http::timeout(15)
                ->retry(3, 100)
                ->post($telegramApiUrl, $payload);

            return $response->json() ?? ['ok' => false, 'error' => 'Empty response'];
        } catch (\Exception $e) {
            Log::error("Telegram API request failed: " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get the full Telegram API URL for a method
     *
     * @param string $method API method
     * @return string
     */
    protected function getTelegramApiUrl(string $method): string
    {
        return "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/$method";
    }
}
