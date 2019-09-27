<?php

namespace Illuminate\Routing\Middleware;

use Closure;
use Illuminate\Contracts\Redis\Factory as Redis;
use Illuminate\Http\Request;
use Illuminate\Redis\Limiters\DurationLimiter;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ThrottleRequestsWithRedis extends ThrottleRequests
{
    /**
     * The timestamp of the end of the current duration.
     *
     * @var int
     */
    public $decaysAt;
    /**
     * The number of remaining slots.
     *
     * @var int
     */
    public $remaining;
    /**
     * The Redis factory implementation.
     *
     * @var Redis
     */
    protected $redis;

    /**
     * Create a new request throttler.
     *
     * @param Redis $redis
     * @return void
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param int|string $maxAttempts
     * @param float|int $decayMinutes
     * @return mixed
     *
     * @throws HttpException
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if ($this->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            throw $this->buildException($key, $maxAttempts);
        }

        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Determine if the given key has been "accessed" too many times.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return mixed
     */
    protected function tooManyAttempts($key, $maxAttempts, $decayMinutes)
    {
        $limiter = new DurationLimiter(
            $this->redis, $key, $maxAttempts, $decayMinutes * 60
        );

        return tap(!$limiter->acquire(), function () use ($limiter) {
            [$this->decaysAt, $this->remaining] = [
                $limiter->decaysAt, $limiter->remaining,
            ];
        });
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int|null $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (is_null($retryAfter)) {
            return $this->remaining;
        }

        return 0;
    }

    /**
     * Get the number of seconds until the lock is released.
     *
     * @param string $key
     * @return int
     */
    protected function getTimeUntilNextRetry($key)
    {
        return $this->decaysAt - $this->currentTime();
    }
}