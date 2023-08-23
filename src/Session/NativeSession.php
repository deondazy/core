<?php

namespace Denosys\Core\Session;

use Denosys\Core\Session\Flash;
use Denosys\Core\Exceptions\SessionException;
use Psr\Container\ContainerInterface;

/**
 * A PHP Session handler adapter.
 */
final class NativeSession implements SessionInterface, SessionManagerInterface
{
    private array $storage;

    private FlashInterface $flash;

    // private array $options = [
    //     'id' => null,
    //     'name' => 'app',
    //     'lifetime' => 7200,
    //     'path' => null,
    //     'domain' => null,
    //     'secure' => false,
    //     'httponly' => true,
    //     // public, private_no_expire, private, nocache
    //     // Setting the cache limiter to '' will turn off automatic sending of cache headers entirely.
    //     'cache_limiter' => 'nocache',
    // ];

    public function __construct(private ContainerInterface $container)
    {
        // Prevent uninitialized state
        $empty = [];
        $this->storage = &$empty;
        $this->flash = new Flash($empty);

        $sessionAttributes = $this->container->get('config')->get('session');

        // $keys = array_keys($sessionAttributes);
        
        // foreach ($keys as $key) {
        //     if (array_key_exists($key, $options)) {
        //         $sessionAttributes[$key] = $options[$key];
        //         unset($options[$key]);
        //     }
        // }

        foreach ($sessionAttributes as $key => $value) {
            ini_set('session.' . $key, $value);
        }

        // Ensure PHP doesn't automatically send the session cookie
        ini_set('session.use_cookies', '0');
    }

    public function start(): void
    {
        if ($this->isStarted()) {
            throw new SessionException('Failed to start the session: Already started.');
        }

        if (headers_sent($file, $line) && filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOLEAN)) {
            throw new SessionException(
                sprintf(
                    'Failed to start the session because headers have already been sent by "%s" at line %d.',
                    $file,
                    $line
                )
            );
        }

        $sessionAttributes = $this->container->get('config')->get('session');

        $current = session_get_cookie_params();

        $lifetime = (int)($sessionAttributes['lifetime'] ?: $current['lifetime']);
        $path = $sessionAttributes['path'] ?: $current['path'];
        $domain = $sessionAttributes['domain'] ?: $current['domain'];
        $secure = $sessionAttributes['secure'];
        $httponly = $sessionAttributes['httponly'];

        $params = [
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => 'Lax',
        ];

        session_set_cookie_params($params);
        session_name($sessionAttributes['name']);
        session_cache_limiter($sessionAttributes['cache_limiter']);

        // $sessionId = $sessionAttributes['id'] ?: null;
        // if ($sessionId) {
        //     session_id($sessionId);
        // }

        // Try and start the session
        if (!session_start()) {
            throw new SessionException('Failed to start the session.');
        }

        // Load the session
        $this->storage = &$_SESSION;
        $this->flash = new Flash($_SESSION);
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function regenerateId(): void
    {
        if (!$this->isStarted()) {
            throw new SessionException('Cannot regenerate the session ID for non-active sessions.');
        }

        if (headers_sent()) {
            throw new SessionException('Headers have already been sent.');
        }

        if (!session_regenerate_id(true)) {
            throw new SessionException('The session ID could not be regenerated.');
        }
    }

    public function destroy(): void
    {
        if (!$this->isStarted()) {
            return;
        }

        $this->clear();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                $this->getName(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if (session_unset() === false) {
            throw new SessionException('The session could not be unset.');
        }

        if (session_destroy() === false) {
            throw new SessionException('The session could not be destroyed.');
        }
    }

    public function getId(): string
    {
        return (string)session_id();
    }

    public function getName(): string
    {
        return (string)session_name();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage[$key] ?? $default;
    }

    public function all(): array
    {
        return (array)$this->storage;
    }

    public function set(string $key, mixed $value): void
    {
        $this->storage[$key] = $value;
    }

    public function setValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->storage[$key] = $value;
        }
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }

    public function delete(string $key): void
    {
        unset($this->storage[$key]);
    }

    public function clear(): void
    {
        $keys = array_keys($this->storage);
        foreach ($keys as $key) {
            unset($this->storage[$key]);
        }
    }

    public function save(): void
    {
        session_write_close();
    }

    public function getFlash(): FlashInterface
    {
        return $this->flash;
    }
}
