<?php

/**
 * Copyright © Webmakkers.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webmakkers\Deploy\module\Plugin;

use Magento\Deploy\Process\Queue;
use Magento\Deploy\Package\Package;
use Webmakkers\Deploy\module\Api\ActiveThemesResolverInterface;

class ExcludeInactiveThemesFromQueue
{
    private ActiveThemesResolverInterface $activeThemesResolver;

    public function __construct(
        ActiveThemesResolverInterface $activeThemesResolver
    ) {
        $this->activeThemesResolver = $activeThemesResolver;
    }

    public function aroundAdd(
        Queue $instance,
        callable $proceed,
        Package $package,
        array $dependencies = []
    ) {
        $activeThemes = $this->activeThemesResolver->execute();

        $code = $package->getTheme();
        if (!in_array($code, $activeThemes)) {
            echo 'Webmakkers_Deploy > Skip unused theme: ' . $package->getPath() . "\r\n";
            return false;
        }

        return $proceed($package, $dependencies);
    }
}
