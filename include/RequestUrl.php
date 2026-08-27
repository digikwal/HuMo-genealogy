<?php

namespace Genealogy\Include;

use InvalidArgumentException;

/**
 * Build absolute URLs from a validated public origin and request data.
 *
 * HUMOGEN_PUBLIC_ORIGIN may be set to a trusted value such as
 * https://genealogy.example.com:8443. Proxy headers are only used when
 * HUMOGEN_TRUST_PROXY_HEADERS is explicitly enabled.
 */
final class RequestUrl
{
    private array $server;
    private ?string $publicOrigin;
    private bool $trustProxyHeaders;

    public function __construct(
        ?array $server = null,
        ?string $publicOrigin = null,
        ?bool $trustProxyHeaders = null
    ) {
        $this->server = $server ?? $_SERVER;

        if ($publicOrigin === null) {
            $configuredOrigin = getenv('HUMOGEN_PUBLIC_ORIGIN', true);
            $publicOrigin = $configuredOrigin === false ? '' : $configuredOrigin;
        }
        $this->publicOrigin = $this->normalizePublicOrigin($publicOrigin);

        if ($trustProxyHeaders === null) {
            $configuredTrust = getenv('HUMOGEN_TRUST_PROXY_HEADERS', true);
            $trustProxyHeaders = $configuredTrust !== false
                && filter_var($configuredTrust, FILTER_VALIDATE_BOOL);
        }
        $this->trustProxyHeaders = $trustProxyHeaders;
    }

    public function getOrigin(): string
    {
        if ($this->publicOrigin !== null) {
            return $this->publicOrigin;
        }

        return $this->getScheme() . '://' . $this->getAuthority();
    }

    public function getUrl(string $path = ''): string
    {
        if ($path === '') {
            return $this->getOrigin();
        }

        return $this->getOrigin() . '/' . ltrim($path, '/');
    }

    public function getCurrentUrl(): string
    {
        $requestUri = $this->server['REQUEST_URI'] ?? '/';
        if (!is_string($requestUri) || $requestUri === '') {
            $requestUri = '/';
        }

        return $this->getUrl($requestUri);
    }

    private function getScheme(): string
    {
        if ($this->trustProxyHeaders) {
            $forwardedProto = $this->firstForwardedValue('HTTP_X_FORWARDED_PROTO');
            if ($forwardedProto === 'http' || $forwardedProto === 'https') {
                return $forwardedProto;
            }
        }

        $https = strtolower((string) ($this->server['HTTPS'] ?? ''));
        return !in_array($https, ['', 'off', '0', 'false', 'no'], true) ? 'https' : 'http';
    }

    private function getAuthority(): string
    {
        $candidates = [];
        if ($this->trustProxyHeaders) {
            $candidates[] = $this->firstForwardedValue('HTTP_X_FORWARDED_HOST');
        }
        $candidates[] = $this->server['HTTP_HOST'] ?? '';

        foreach ($candidates as $candidate) {
            $authority = $this->normalizeAuthority((string) $candidate);
            if ($authority !== null) {
                return $authority;
            }
        }

        $serverName = (string) ($this->server['SERVER_NAME'] ?? 'localhost');
        if (filter_var($serverName, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $serverName = '[' . $serverName . ']';
        }

        $serverPort = filter_var(
            $this->server['SERVER_PORT'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 65535]]
        );
        if ($serverPort !== false && !$this->isDefaultPort($serverPort)) {
            $serverName .= ':' . $serverPort;
        }

        return $this->normalizeAuthority($serverName) ?? 'localhost';
    }

    private function normalizePublicOrigin(string $origin): ?string
    {
        $origin = trim($origin);
        if ($origin === '') {
            return null;
        }

        try {
            $parts = parse_url($origin);
        } catch (\ValueError) {
            $parts = false;
        }
        if (
            $parts === false
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])
            || (isset($parts['path']) && $parts['path'] !== '' && $parts['path'] !== '/')
        ) {
            throw new InvalidArgumentException(
                'HUMOGEN_PUBLIC_ORIGIN must contain only an http(s) scheme, host, and optional port.'
            );
        }

        $authority = $parts['host'];
        if (filter_var($authority, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $authority = '[' . $authority . ']';
        }
        if (isset($parts['port'])) {
            $authority .= ':' . $parts['port'];
        }

        $authority = $this->normalizeAuthority($authority);
        if ($authority === null) {
            throw new InvalidArgumentException('HUMOGEN_PUBLIC_ORIGIN contains an invalid host or port.');
        }

        return strtolower($parts['scheme']) . '://' . $authority;
    }

    private function normalizeAuthority(string $authority): ?string
    {
        $authority = trim($authority);
        if ($authority === '' || preg_match('/[\x00-\x20\x7F]/', $authority)) {
            return null;
        }

        if (preg_match('/^\[([^]]+)](?::([0-9]{1,5}))?$/', $authority, $matches)) {
            if (!filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return null;
            }
            $host = '[' . strtolower($matches[1]) . ']';
            $port = $matches[2] ?? '';
        } elseif (preg_match('/^([^:]+)(?::([0-9]{1,5}))?$/', $authority, $matches)) {
            $host = strtolower($matches[1]);
            if (
                !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                && !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            ) {
                return null;
            }
            $port = $matches[2] ?? '';
        } else {
            return null;
        }

        if ($port !== '') {
            $validatedPort = filter_var(
                $port,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 65535]]
            );
            if ($validatedPort === false) {
                return null;
            }
            return $host . ':' . $validatedPort;
        }

        return $host;
    }

    private function firstForwardedValue(string $key): string
    {
        $value = $this->server[$key] ?? '';
        if (!is_string($value)) {
            return '';
        }

        return strtolower(trim(explode(',', $value)[0]));
    }

    private function isDefaultPort(int $port): bool
    {
        return ($this->getScheme() === 'http' && $port === 80)
            || ($this->getScheme() === 'https' && $port === 443);
    }
}
