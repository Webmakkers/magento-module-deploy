<?php

/**
 * Copyright © Webmakkers.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webmakkers\Deploy\Api;

interface ActiveThemesResolverInterface
{
    public function execute(): array;
}
