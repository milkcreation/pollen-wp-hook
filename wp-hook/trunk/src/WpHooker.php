<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use InvalidArgumentException;
use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\WpPost\WpPostProxy;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;

class WpHooker implements WpHookerInterface
{
    use BootableTrait;
    use ConfigBagAwareTrait;
    use ContainerProxy;
    use WpPostProxy;

    /**
     * Instance principale.
     * @var static|null
     */
    private static $instance;

    /**
     * Activation de l'exécution de stockage en base de données.
     * @var bool
     */
    private $storage = false;

    /**
     * Dépôt de contenu d'accroches.
     * @var WpHookableInterface[]
     */
    protected $hooks = [];

    /**
     * Liste des éléments d'accroche.
     * @var ParamsBag
     */
    protected $hooksBag;

    /**
     * Cartographie des noms de qualification d'accroche.
     * @var string[]
     */
    protected $hooksMap = [];

    /**
     * Cartographie des options.
     * @var string[]
     */
    protected $optionsMap = [];

    /**
     * @param array $config
     * @param Container|null $container
     */
    public function __construct(array $config = [], ?Container $container = null)
    {
        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        if ($this->config('boot_enabled', true)) {
            $this->boot();
        }

        if (!self::$instance instanceof static) {
            self::$instance = $this;
        }
    }

    /**
     * Récupération de l'instance principale.
     *
     * @return static
     */
    public static function getInstance(): WpHookerInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new RuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function addHookOption(string $hook, string $option): WpHookerInterface
    {
        if (!isset($this->hooksMap[$hook])) {
            $this->hooksMap[$hook] = $option;
            $this->optionsMap[$option] = $hook;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function boot(): WpHookerInterface
    {
        if (!$this->isBooted()) {
            add_action('admin_init', function () {
                foreach (array_keys($this->optionsMap) as $option) {
                    add_filter("sanitize_option_{$option}", [$this, 'saveOption'], 999999, 3);
                }
            });

            $this->setBooted();
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $hook): ?WpHookableInterface
    {
        if(isset($this->hooks[$hook])) {
            return $this->hooks[$hook];
        }

        if (($args = $this->getHooksBag($hook)) && isset($args['id'], $args['path'])) {
            return $this->hooks[$hook] = new WpHookable($args['id'], $args['path'], $this);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHooksBag($key = null, $default = null)
    {
        if ($this->hooksBag === null) {
            $this->hooksBag = new ParamsBag(get_option('hooks_bag') ?: []);
        }

        if ($key === null) {
            return $this->hooksBag;
        }

        if (is_string($key)) {
            return $this->hooksBag->get($key, $default);
        }

        if (is_array($key)) {
            $this->hooksBag->set($key);
            return $this->hooksBag;
        }

        throw new InvalidArgumentException('Invalid HooksBag passed method arguments');
    }

    /**
     * Déclaration d'un contenu d'accroche.
     *
     * @param string $hook
     * @param int $post_id
     * @param string $path
     *
     * @return void
     */
    protected function registerHookBag(string $hook, int $post_id, string $path): void
    {
        if ($this->storage === false) {
            register_shutdown_function([$this, 'storeHooksBag']);
            $this->storage = true;
        }
        $this->getHooksBag([$hook => ['id' => $post_id, 'path' => $path]]);
    }

    /**
     * @inheritDoc
     */
    public function saveOption($value, string $option, $original_value)
    {
        if (is_numeric($value) &&
            $value > 0 &&
            ($hook = $this->optionsMap[$option] ?? null) &&
            ($post = $this->wpPost()->post($value))
        ) {
            $this->registerHookBag($hook, $post->getId(), $post->getPath());
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function storeHooksBag(): void
    {
        if ($hooksBag = $this->getHooksBag()) {
            update_option('hooks_bag', $hooksBag->all());
        }
    }
}
