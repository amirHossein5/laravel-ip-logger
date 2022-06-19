<?php

namespace AmirHossein5\LaravelIpLogger\Tests\Feature;

use AmirHossein5\LaravelIpLogger\Events\Failed;
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;
use AmirHossein5\LaravelIpLogger\Tests\Models\Ip;
use AmirHossein5\LaravelIpLogger\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ExceptionTest extends TestCase
{
    public function test_catch_method()
    {
        Event::fake();

        config(['ipLogger.get_details_from' => 'vpn_api']);
        config(['ipLogger.vpn_api_key' => null]);

        $this->assertFalse(IpLogger::catch(fn ($exception) => $this->assertFalse(blank($exception)))->getDetails());
        Event::assertNotDispatched(Failed::class);
        $this->assertIsObject(IpLogger::getLastException());

        Event::fake();

        $this->assertFalse(IpLogger::model(Ip::class)->getDetails());

        Event::assertDispatched(Failed::class);
        $this->assertNotEmpty(IpLogger::getLastException());

        Event::fake();

        //  should come first
        $this->assertFalse(IpLogger::model(Ip::class)->detailsBe(function () {
            throw \Exception('');
        })->catch(fn ($exception) => $exception)->getDetails());

        Event::assertDispatched(Failed::class);
        $this->assertNotEmpty(IpLogger::getLastException());

        Event::fake();
        config(['ipLogger.get_details_from' => 'ip_api']);

        $this->assertFalse(IpLogger::catch(fn ($e) => $e)
            ->detailsBe(function () {
                throw \Exception('');
            })->getDetails());

        Event::assertNotDispatched(Failed::class);
        $this->assertNotEmpty(IpLogger::getLastException());

        Event::fake();

        $this->assertFalse(IpLogger::catch(fn ($exception) => $exception->getMessage())
            ->model(Ip::class)
            ->detailsBe(function () {
                [];
            })->prepare(function () {
                throw \Exception('');
            })->getDetails());

        Event::assertNotDispatched(Failed::class);
        $this->assertNotEmpty(IpLogger::getLastException());
    }

    public function test_when_exception_happend_wont_effect_on_next_calls()
    {
        Event::fake();

        config(['ipLogger.get_details_from' => 'vpn_api']);
        config(['ipLogger.vpn_api_key' => null]);

        $this->assertFalse(IpLogger::catch(fn ($exception) => $this->assertFalse(blank($exception)))->getDetails());
        Event::assertNotDispatched(Failed::class);
        $this->assertIsObject(IpLogger::getLastException());

        config(['ipLogger.get_details_from' => 'ip_api']);

        $this->assertIsArray(IpLogger::getDetails());
        $this->assertIsObject(IpLogger::getLastException());
    }
}
