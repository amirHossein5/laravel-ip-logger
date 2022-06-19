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

    /**
     * Details of the ip.
     * 
     * @var null|object|array
     */
    private null|array|object $details = null;

    /**
     * Exception that happend when getting details of the ip.
     * 
     * @var null|string|\Exception
     */
    private null|string|\Exception $exception = null;

    /**
     * Sets the details of the ip.
     * 
     * @param array|\Closure $details
     * 
     * @return self
     */
    public function detailsBe(array|Closure $details): self
    {
        try {
            if ($details instanceof \Closure) {
                $this->details = $details();
            } else {
                $this->details = $details;
            }
        } catch (\Throwable $e) {
            $this->exception = $e;

            event(new Failed($e));
        }

        return $this;
    }

    /**
     * Developer can edit the fetched details.
     * 
     * @param \Closure $details
     * 
     * @return self
     */
    public function prepare(Closure $details): self
    {
        try {
            if ($getDetails = $this->getDetails()) {
                $this->details = $details($getDetails);
            }
        } catch (\Throwable $e) {
            $this->exception = $e;

            event(new Failed($e));
        }

        return $this;
    }

    /**
     * Gets the details of ip.
     * 
     * @return bool|array
     */
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

    /**
     * Returns the last exception that happened.
     * 
     * @return null|string|\Exception
     */
    public function getLastException(): null|string|\Exception
    {
        return $this->exception;
    }

    /**
     * Fetches details of the ip.
     * 
     * @return null|array
     */
    private function fetchDetails(): ?array
    {
        try {
            $ip = $this->getIp();
            $from = config('ipLogger.get_details_from');

            return $this->$from($ip);
        } catch (\Throwable $e) {
            $this->exception = $e;

            return event(new Failed($e));
        }
    }

    /**
     * Gets the ip.
     * 
     * @return null|array|string
     */
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

    /**
     * Resets the properties of the class.
     * 
     * @return void
     */
    private function resetProps(): void
    {
        $whiteList = [
            'exception',
        ];

        foreach (get_class_vars(get_class($this)) as $var => $def_val) {
            if (!in_array($var, $whiteList)) {
                $this->$var = $def_val;
            }
        }
    }
}
