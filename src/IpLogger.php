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
    use Catchable;

    /**
     * Details of the ip.
     * 
     * @var null|object|array
     */
    private null|array|object $details = null;

    /**
     * Exception that happend on the current process of getting ip.
     * 
     * @var null|string|\Exception
     */
    private null|string|\Exception $exception = null;

    /**
     * Last exception that happend.
     * 
     * @var null|string|\Exception
     */
    private null|string|\Exception $lastException = null;

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
            $this->exception($e);

            $this->notifyException($e);
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
            if ($getDetails = $this->details()) {
                $this->details = $details($getDetails);
            }
        } catch (\Throwable $e) {
            $this->exception($e);

            $this->notifyException($e);
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
            $this->resetProps();
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
        return $this->lastException;
    }

    /**
     * Gets the details of ip without reseting the properties.
     * For using in this class for preventing being properties reset.
     * 
     * @return bool|array
     */
    public function details(): bool|array
    {
        if (!$this->details) {
            $this->details = $this->fetchDetails();
        }

        if ($this->exception) {
            return false;
        }

        $details = $this->details;

        return $details;
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
            $this->exception($e);

            $this->notifyException($e);

            return null;
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
     * Sets exception for both exception and lastException properties.
     * 
     * @param \Throwable $e
     * 
     * @return void
     */
    public function exception(\Throwable $e): void
    {
        $this->exception = $e;
        $this->lastException = $e;
    }

    /**
     * Sends exception to event or something else.
     * 
     * @param \Throwable $exception
     * 
     * @return mixed
     */
    private function notifyException(\Throwable $exception): mixed
    {
        if ($this->catch !== null) {
            $catch = $this->catch;
            return $catch($exception);
        } else {
            return event(new Failed($exception));
        }
    }

    /**
     * Resets the properties of the class.
     * 
     * @return void
     */
    private function resetProps(): void
    {
        $whiteList = [
            'lastException',
        ];

        foreach (get_class_vars(get_class($this)) as $var => $def_val) {
            if (!in_array($var, $whiteList)) {
                $this->$var = $def_val;
            }
        }
    }
}
