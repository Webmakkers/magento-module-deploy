<?php

/**
 * Copyright © Webmakkers.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webmakkers\Deploy\Plugin;

use Magento\Deploy\Process\Queue;
use Magento\Deploy\Package\Package;
use Webmakkers\Deploy\Api\ActiveThemesResolverInterface;

readonly class ExcludeInactiveThemesFromQueue
{
    public function __construct(
        private ActiveThemesResolverInterface $activeThemesResolver
    ) {
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
