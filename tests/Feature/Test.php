<?php

namespace AmirHossein5\LaravelIpLogger\Tests\Feature;

use AmirHossein5\LaravelIpLogger\Events\Failed;
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;
use AmirHossein5\LaravelIpLogger\Tests\Models\Ip;
use AmirHossein5\LaravelIpLogger\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class Test extends TestCase
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

    public function test_update_or_create_method()
    {
        $this->assertDatabaseCount('ip_details', 0);

        config(['ipLogger.get_details_from' => 'ip_api']);

        $record = IpLogger::model(Ip::class)
            ->updateOrCreate(
                fn ($details) => [
                    'ip'               => $details['query'],
                ],
                fn ($details) => [
                    'security'         => [$details['proxy'], $details['mobile']],
                    'continent'        => $details['continent'],
                    'country'          => $details['country'],
                    'timezone'         => $details['timezone'],
                    'internetProvider' => $details['isp'],
                    'visited_at'       => now(),
                    'seen'             => false,
                ],
            );

        $this->assertDatabaseCount('ip_details', 1);

        $this->assertTrue(DB::table('ip_details')
            ->where('ip', $record->ip)
            ->exists());

        $record = IpLogger::model(Ip::class)
            ->updateOrCreate(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                    ];
                },
                function ($details) {
                    return [
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 1);

        $this->assertTrue(DB::table('ip_details')
            ->where('ip', $record->ip)
            ->exists());
    }

    public function test_update_or_create_returns_false_when_has_exception_method()
    {
        Event::fake();

        config(['ipLogger.get_details_from' => 'vpn_api']);
        config(['ipLogger.vpn_api_key' => null]);

        $this->assertDatabaseCount('ip_details', 0);

        $record = IpLogger::model(Ip::class)
            ->updateOrCreate(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                    ];
                },
                function ($details) {
                    return [
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 0);
        Event::assertDispatched(Failed::class);
        $this->assertFalse($record);

        $record = IpLogger::model(Ip::class)
            ->updateOrCreate(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                    ];
                },
                function ($details) {
                    return [
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 0);
        Event::assertDispatched(Failed::class);
        $this->assertFalse($record);
    }

    public function test_create_method()
    {
        $this->assertDatabaseCount('ip_details', 0);

        config(['ipLogger.get_details_from' => 'ip_api']);

        $record = IpLogger::model(Ip::class)
            ->create(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 1);

        $this->assertTrue(DB::table('ip_details')
            ->where('ip', $record->ip)
            ->exists());

        $record = IpLogger::model(Ip::class)
            ->create(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 2);
    }

    public function test_create_returns_false_when_has_exception_method()
    {
        Event::fake();

        config(['ipLogger.get_details_from' => 'vpn_api']);
        config(['ipLogger.vpn_api_key' => null]);

        $this->assertDatabaseCount('ip_details', 0);

        $record = IpLogger::model(Ip::class)
            ->create(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 0);
        Event::assertDispatched(Failed::class);
        $this->assertFalse($record);

        $record = IpLogger::model(Ip::class)
            ->create(
                function ($details) {
                    return [
                        'ip'               => $details['query'],
                        'security'         => [$details['proxy'], $details['mobile']],
                        'continent'        => $details['continent'],
                        'country'          => $details['country'],
                        'timezone'         => $details['timezone'],
                        'internetProvider' => $details['isp'],
                        'visited_at'       => now(),
                        'seen'             => false,
                    ];
                }
            );

        $this->assertDatabaseCount('ip_details', 0);
        Event::assertDispatched(Failed::class);
        $this->assertFalse($record);
    }
}
