<?php

namespace Tests\Unit;

use App\Services\TelegramBotService;
use Tests\TestCase;

class TelegramBotServiceTest extends TestCase
{
    public function test_missing_credentials_disable_telegram_requests(): void
    {
        config([
            'services.telegram.bot_token' => null,
            'services.telegram.chat_id' => null,
        ]);

        $service = new TelegramBotService();

        $this->assertFalse($service->isConfigured());
        $this->assertFalse($service->sendMessage(123456, 'test'));
        $this->assertFalse($service->sendToDefaultChat('test'));
        $this->assertNull($service->getBotInfo());
    }
}
