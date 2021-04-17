<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use InvalidArgumentException;
use Pollen\Routing\RouteInterface;
use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Exception\ManagerRuntimeException;
use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\ContainerProxy;
use Pollen\WpPost\WpPostProxy;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use WP_Post;

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
     * Instance du conteneur d'éléments d'accroche.
     * @var ParamsBag
     */
    private $hooksBag;

    /**
     * Activation de l'exécution de stockage en base de données.
     * @var bool
     */
    private $storage = false;

    /**
     * Dépôt des contenus d'accroche déclarés et instanciés.
     * @var WpHookableInterface[]
     */
    protected $hooks = [];

    /**
     * Liste des identifiants de qualification des éléments d'accroche déclarés.
     * @var int[]
     */
    protected $ids = [];

    /**
     * Liste des chemins des éléments d'accroche déclarés.
     * @var string[]
     */
    protected $paths = [];

    /**
     * Liste des routes associées aux éléments d'accroche déclarés.
     * @var RouteInterface[]|array
     */
    protected $routes = [];

    /**
     * Cartographie des options.
     * @var string[]
     */
    protected $optionsMap = [];

    /**
     * Cartographie de paramètres des éléments d'accroche.
     * @var array[]
     */
    protected $paramsMap = [];

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
        throw new ManagerRuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function addHookOption(string $hook, string $option, array $params = []): WpHookerInterface
    {
        if (!isset($this->optionsMap[$option])) {
            $this->optionsMap[$option] = $hook;

            if ($params) {
                $this->paramsMap[$hook] = $params;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function boot(): WpHookerInterface
    {
        if (!$this->isBooted()) {
            add_action(
                'admin_init',
                function () {
                    foreach (array_keys($this->optionsMap) as $option) {
                        add_filter("sanitize_option_{$option}", [$this, 'saveOption'], 999999, 3);
                    }
                }
            );

            add_filter('display_post_states', function (array $post_states, WP_Post $post) {
                if (($hook = $this->getById((int)$post->ID)) && ($state = $hook->getPostState())) {
                    $post_states[] = $state;
                }
                return $post_states;
            }, 10, 2);

            add_action('edit_form_top', function (WP_Post $post) {
                if (($hook = $this->getById((int)$post->ID)) && ($notice = $hook->getEditNotice())) {
                    echo "<div class=\"notice notice-info inline\">\n\t<p>$notice</p>\n</div>";
                }
            });

            add_action('save_post', function (int $post_id) {
                if (($hook = $this->getById($post_id))) {
                    $post = $this->wpPost($post_id);
                    $this->registerHookBag($hook->getName(), $post_id, $post->getPath());
                }
            });

            $this->setBooted();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->getHooksBag()->all();
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): ?WpHookableInterface
    {
        if (isset($this->hooks[$name])) {
            return $this->hooks[$name];
        }

        if (($args = $this->getHooksBag($name)) && isset($args['id'], $args['path'])) {
            $hookable = new WpHookable($name, $args['id'], $args['path'], $this);
            $hookable->setParams($this->paramsMap[$name] ?? []);

            return $this->hooks[$name] = $hookable;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): ?WpHookableInterface
    {
        $ids = $this->getIds();

        if ($name = $ids[$id] ?? null) {
            return $this->get($name);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getHookNames(): array
    {
        return $this->getHooksBag()->keys();
    }

    /**
     * Définition|Récupération|Instance des paramètres d'accroche.
     *
     * @param array|string|null $key
     * @param mixed $default
     *
     * @return string|int|array|mixed|ParamsBag
     *
     * @throws InvalidArgumentException
     */
    protected function getHooksBag($key = null, $default = null)
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
     * @inheritDoc
     */
    public function getIds(): array
    {
        if ($this->ids && !array_diff(array_keys($this->ids), $this->getHookNames())) {
            return $this->ids;
        }

        foreach ($this->all() as $name => $attrs) {
            if ($id = $attrs['id'] ?? 0) {
                $this->ids[$id] = $name;
            }
        }

        return $this->ids;
    }

    /**
     * @inheritDoc
     */
    public function getPaths(): array
    {
        if (!array_diff(array_keys($this->paths), $this->getHookNames())) {
            return $this->paths;
        }

        foreach ($this->all() as $name => $attrs) {
            if ($path = $attrs['path'] ?? '') {
                $this->paths[$name] = $path;
            }
        }

        return $this->paths;
    }

    /**
     * @inheritDoc
     */
    public function getRoute(string $name): ?RouteInterface
    {
        return $this->routes[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getRouteHookable(RouteInterface $route): ?WpHookableInterface
    {
        $name = array_search($route, $this->routes, true);

        if ($name !== false) {
            return $this->get($name);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasId(int $id): bool
    {
        return in_array($id, $this->getIds(), true);
    }

    /**
     * @inheritDoc
     */
    public function hasPath(string $path): bool
    {
        return in_array($path, $this->getPaths(), true);
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
            ($post = $this->wpPost($value))
        ) {
            $this->registerHookBag($hook, $post->getId(), $post->getPath());
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function setRoute(WpHookableInterface $hookable, RouteInterface $route): WpHookerInterface
    {
        $exists = array_search($route, $this->routes, true);

        if ($exists === false) {
            $this->routes[$hookable->getName()] = $route;

            return $this;
        }
        throw new RuntimeException(
            sprintf(
                'WpHooker could not associating route with hook [%s]. This route is already used by the hook [%s]',
                $hookable->getName(),
                $exists
            )
        );
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
