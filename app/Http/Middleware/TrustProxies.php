<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Solo loopback: es donde termina TLS (nginx/proxy local) antes de llegar
     * a PHP-FPM. Sin esto, `X-Forwarded-Proto: https` se ignora y la app
     * cree que va por HTTP (no emitiría HSTS ni generaría URLs https) — R1.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = ['127.0.0.1', '::1'];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
