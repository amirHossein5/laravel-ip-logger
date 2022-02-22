<?php

namespace AmirHossein5\LaravelIpLogger;

use AmirHossein5\LaravelIpLogger\Events\Failed;
use AmirHossein5\LaravelIpLogger\Exceptions\ConnectionFailedException;
use Closure;
use Illuminate\Support\Facades\Http;

class IpLogger
{
    use GetDetailsFrom;
    use Eloquent;

    private null|array|object $details = null;
    private null|string|\Exception $exception = null;

    public function detailsBe(array|Closure $details): self
    {
        $this->details = $details();

        return $this;
    }

    public function prepare(Closure $details): self
    {
        if ($getDetails = $this->getDetails()) {
            $this->details = $details($getDetails);
        }

        return $this;
    }

    public function getDetails(): bool|array
    {
        if (!$this->details) {
            $this->details = $this->fetchDetails();
        }

        if ($this->exception) {
            return false;
        }

        $details = $this->details;
        $this->resetProps();

        return $details;
    }

    public function getLastException(): null|string|\Exception
    {
        return $this->exception;
    }

    private function fetchDetails(): ?array
    {
        try {
            $ip = $this->getIp();
            $from = config('ipLogger.get_details_from');

            return $this->$from($ip);
        } catch (\Exception $e) {
            $this->exception = $e;

            return event(new Failed($e));
        }
    }

    private function getIp(): null|array|string
    {
        $ip = null;

        if (config('app.env') === 'production') {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                //ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                //ip pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            $ip = Http::get('https://api.ipify.org?format=json')->object()?->ip;  //for local
        }

        if (!$ip) {
            throw new ConnectionFailedException("Can't get ip.");
        }

        return $ip;
    }

    private function resetProps(): void
    {
        foreach (get_class_vars(get_class($this)) as $var => $def_val) {
            if ($var !== 'exception') {
                $this->$var = $def_val;
            }
        }
    }
}
