<?php

namespace AmirHossein5\LaravelIpLogger\Tests\Feature;

use AmirHossein5\LaravelIpLogger\Events\Failed;
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;
use AmirHossein5\LaravelIpLogger\Tests\Models\Ip;
use AmirHossein5\LaravelIpLogger\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class ModifyDetailsTest extends TestCase
{
    public function test_details_can_be_changed()
    {
        Event::fake();

        $details = IpLogger::detailsBe(function () {
            return ['test' => 's'];
        })->getDetails();

        Event::assertNotDispatched(Failed::class);
        $this->assertTrue(isset($details['test']));
        $this->assertTrue($details['test'] === 's');

        $details = IpLogger::detailsBe(function () {
            return ['test' => 's'];
        })->prepare(function ($details) {
            return $details + ['name' => 'ss'];
        })->getDetails();

        Event::assertNotDispatched(Failed::class);
        $this->assertTrue(isset($details['test']));
        $this->assertTrue(isset($details['name']));
        $this->assertTrue($details['test'] === 's');
        $this->assertTrue($details['name'] === 'ss');

        config(['ipLogger.get_details_from' => 'ip_api']);

        $details = IpLogger::model(Ip::class)
            ->prepare(function ($details) {
                return $details + ['name' => 'ss'];
            })->getDetails();

        Event::assertNotDispatched(Failed::class);
        $this->assertTrue(isset($details['query']));
        $this->assertTrue(isset($details['name']));
        $this->assertTrue($details['name'] === 'ss');

        config(['ipLogger.get_details_from' => 'vpn_api']);

        $details = IpLogger::model(Ip::class)
            ->prepare(function ($details) {
                return $details + ['name' => 'ss'];
            })->getDetails();

        Event::assertDispatched(Failed::class);
        $this->assertFalse($details);
    }

    public function test_detailsBe_and_prepare_throws_exception_to_event()
    {
        Event::fake();

        $details = IpLogger::model(Ip::class)
            ->detailsBe(function () {
                return ['test' => 's'];
            })
            ->prepare(function ($details) {
                throw \Exception('');

                return $details + ['name' => 'ss'];
            })->getDetails();

        Event::assertDispatched(Failed::class);
        $this->assertFalse($details);

        $details = IpLogger::model(Ip::class)
            ->detailsBe(function () {
                throw \Exception('');

                return ['test' => 's'];
            })
            ->prepare(function ($details) {
                return $details + ['name' => 'ss'];
            })->getDetails();

        Event::assertDispatched(Failed::class);
        $this->assertFalse($details);

        config(['ipLogger.get_details_from' => 'vpn_api']);

        $details = IpLogger::model(Ip::class)
            ->prepare(function ($details) {
                return $details + ['name' => 'ss'];
            })->getDetails();

        Event::assertDispatched(Failed::class);
        $this->assertFalse($details);
    }
}
