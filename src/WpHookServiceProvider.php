<?php

declare(strict_types=1);

namespace Pollen\WpHook;

use Pollen\Container\BaseServiceProvider;

class WpHookServiceProvider extends BaseServiceProvider
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