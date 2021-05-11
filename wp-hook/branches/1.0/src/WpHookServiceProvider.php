<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\Container\BootableServiceProvider;

class WpHookServiceProvider extends BootableServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        WpHookerInterface::class
    ];

    /**
     * @inheritdoc
     */
    public function register(): void
    {
        $this->getContainer()->share(WpHookerInterface::class, function() {
            return new WpHooker([], $this->getContainer());
        });
    }
}