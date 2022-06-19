<?php

namespace AmirHossein5\LaravelIpLogger\Tests\Feature;

use AmirHossein5\LaravelIpLogger\Events\Failed;
use AmirHossein5\LaravelIpLogger\Facades\IpLogger;
use AmirHossein5\LaravelIpLogger\Tests\Models\Ip;
use AmirHossein5\LaravelIpLogger\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class EloquentTest extends TestCase
{
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

    public function test_eloquent_exceptions_wont_be_handle()
    {
        $this->expectException(\PDOException::class);
        IpLogger::model(Ip::class)
            ->create(function () {
                return [];
            });

        $this->expectException(\PDOException::class);
        IpLogger::model(Ip::class)
            ->updateOrCreate(function () {
                return [];
            });
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