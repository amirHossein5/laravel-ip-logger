<?php

namespace AmirHossein5\LaravelIpLogger\Tests\Feature;

use AmirHossein5\LaravelIpLogger\Events\Failed;
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;
use AmirHossein5\LaravelIpLogger\Tests\Models\Ip;
use AmirHossein5\LaravelIpLogger\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class BaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_ip_logger_get_details_with_multiple_apis()
    {
        Event::fake();
        config(['ipLogger.get_details_from' => 'ip_api']);

        $this->assertIsArray(IpLogger::getDetails());
        Event::assertNotDispatched(Failed::class);

        config(['ipLogger.get_details_from' => 'vpn_api']);
        config(['ipLogger.vpn_api_key' => null]);

        $this->assertFalse(IpLogger::getDetails());
        Event::assertDispatched(Failed::class);
    }

    public function test_last_exception_can_be_gotten()
    {
        Event::fake();

        config(['ipLogger.get_details_from' => 'vpn_api']);
        config(['ipLogger.vpn_api_key' => null]);

        $this->assertFalse(IpLogger::getDetails());
        Event::assertDispatched(Failed::class);
        $this->assertIsObject(IpLogger::getLastException());

        IpLogger::model(Ip::class)->getDetails();

        $this->assertFalse(IpLogger::getDetails());
        $this->assertFalse(IpLogger::getDetails());
        Event::assertDispatched(Failed::class);
        $this->assertIsObject(IpLogger::getLastException());
    }
}
