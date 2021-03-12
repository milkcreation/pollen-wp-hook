<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use InvalidArgumentException;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\ContainerProxyInterface;
use Pollen\WpPost\WpPostProxyInterface;

interface WpHookerInterface extends
    BootableTraitInterface,
    ConfigBagAwareTraitInterface,
    ContainerProxyInterface,
    WpPostProxyInterface
{
    /**
     * Ajout d'un couple nom d'accroche <> nom d'option d'enregistrement.
     *
     * @param string $hook
     * @param string $option
     *
     * @return static
     */
    public function addHookOption(string $hook, string $option): WpHookerInterface;

    /**
     * Chargement.
     *
     * @return static
     */
    public function boot(): WpHookerInterface;

    /**
     * Récupération du conteneur d'accroche.
     *
     * @param string $hook
     *
     * @return WpHookableInterface|null
     */
    public function get(string $hook): ?WpHookableInterface;

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
    public function getHooksBag($key = null, $default = null);

    /**
     * Enregistrement des infos de routage dans le hooks_bag
     *
     * @param mixed $value
     * @param string $option
     * @param mixed $original_value
     *
     * @return mixed
     */
    public function saveOption($value, string $option, $original_value);

    /**
     * Enregistrement des valeurs du hooksBag en base de données.
     *
     * @return void
     */
    public function storeHooksBag(): void;
}
