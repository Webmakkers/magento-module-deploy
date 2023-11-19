<?php

/**
 * Copyright © Webmakkers.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Webmakkers\Deploy\module\Models;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Webmakkers\Deploy\module\Api\ActiveThemesResolverInterface;
use function in_array;
use function is_numeric;

class ActiveThemesResolver implements ActiveThemesResolverInterface
{
    private DesignInterface $design;
    private ScopeConfigInterface $scopeConfig;
    private StoreRepositoryInterface $storeRepository;
    private ThemeProviderInterface $themeProvider;
    private $usedThemes;

    public function __construct(
        DesignInterface $design,
        ScopeConfigInterface $scopeConfig,
        StoreRepositoryInterface $storeRepository,
        ThemeProviderInterface $themeProvider
    ) {
        $this->design = $design;
        $this->scopeConfig = $scopeConfig;
        $this->storeRepository = $storeRepository;
        $this->themeProvider = $themeProvider;
    }

    public function execute(): array
    {
        if ($this->usedThemes !== null) {
            return $this->usedThemes;
        }

        $this->usedThemes = [];

        $areaCode = $this->getAreaCode();
        $this->design->setArea('adminhtml');

        $this->addBackend();
        $this->addFrontend();

        $this->design->setArea($areaCode);

        return $this->usedThemes;
    }

    private function getAreaCode(): ?string
    {
        try {
            return $this->design->getArea();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function addBackend(): void
    {
        $this->usedThemes[] = $this->design->getConfigurationDesignTheme();
    }

    private function addFrontend(): void
    {
        $stores = $this->storeRepository->getList();
        if (empty($stores)) {
            return;
        }

        foreach ($stores as $store) {
            $this->addStore($store);
        }
    }

    private function addStore(StoreInterface $store): void
    {
        $themeId = $this->scopeConfig->getValue(
            DesignInterface::XML_PATH_THEME_ID,
            ScopeInterface::SCOPE_STORES,
            (int)$store->getId()
        );

        if (!is_numeric($themeId)) {
            return;
        }

        $theme = $this->themeProvider->getThemeById((int)$themeId);
        if (
            empty($theme->getCode())
            || in_array($theme->getCode(), $this->usedThemes)
        ) {
            return;
        }

        $this->usedThemes[] = $theme->getCode();
    }
}
