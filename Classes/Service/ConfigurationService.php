<?php

declare(strict_types=1);

namespace FriendsOfTYPO3\PwaManifest\Service;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Config\Resource\FileResource;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use function array_filter;

/***
 *
 * This file is part of the "pwa_manifest" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 *
 *  (c) 2019
 *
 ***/

/**
 * Settings service
 */
class ConfigurationService
{
    private ?ServerRequestInterface $request = null;

    /**
     * @param string $content
     * @param array $conf
     * @param ServerRequestInterface $request
     * @return string
     * @throws InvalidFileException
     * @throws ResourceDoesNotExistException
     */
    public function provideConfiguration(string $content, array $conf, ServerRequestInterface $request): string
    {
        $this->request = $request;
        $siteConfiguration = $this->getSite()->getConfiguration();
        $settings = [
            'short_name' => $siteConfiguration['manifestShortName'] ?? '',
            'name' => $siteConfiguration['manifestName'] ?? '',
            'icons' => [
                [
                    'src' => $this->getPathForSrc($siteConfiguration['manifestSmallIconSource'] ?? ''),
                    'type' => $siteConfiguration['manifestSmallIconType'] ?? '',
                    'sizes' => $siteConfiguration['manifestSmallIconSizes'] ?? '',
                ],
                [
                    'src' => $this->getPathForSrc($siteConfiguration['manifestBigIconSource'] ?? ''),
                    'type' => $siteConfiguration['manifestBigIconType'] ?? '',
                    'sizes' => $siteConfiguration['manifestBigIconSizes'] ?? '',
                ]
            ],
            'start_url' => $siteConfiguration['manifestStartUrl'] ?? '',
            'background_color' => $siteConfiguration['manifestBackgroundColor'] ?? '',
            'display' => $siteConfiguration['manifestDisplay'] ?? '',
            'scope' => $siteConfiguration['manifestScope'] ?? '',
            'theme_color' => $siteConfiguration['manifestThemeColor'] ?? '',
            'shortcuts' => $this->getShortcutsConfiguration($siteConfiguration)
        ];

        return json_encode($settings);
    }

    /**
     * @return Site
     */
    protected function getSite(): Site
    {
        return $this->request->getAttribute('site');
    }

    /**
     * @param string $src
     * @return string
     * @throws InvalidFileException
     * @throws ResourceDoesNotExistException
     */
    protected function getPathForSrc(string $src): string
    {
        // If the src is an extension path, we need to get the public path
        if (PathUtility::isExtensionPath($src)) {
            return PathUtility::getPublicResourceWebPath($src);
        }
        // If not an extension path, we need to get the public path of the file
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $file = $resourceFactory->retrieveFileOrFolderObject($src);
        // If the file is not found , we return an empty string
        if($file === null || $file instanceof Folder) {
            return '';
        }
        return $file->getPublicUrl();
    }

    /**
     * @param array $siteConfiguration
     * @return array
     * @throws InvalidFileException
     * @throws ResourceDoesNotExistException
     */
    protected function getShortcutsConfiguration(array $siteConfiguration): array
    {
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $shortcuts = [];
        for ($i = 1; $i <= 3; $i++) {
            if (
                ($siteConfiguration["manifestShortcuts{$i}Name"] ?? '') !== '' &&
                ($siteConfiguration["manifestShortcuts{$i}Url"] ?? '') !== ''
            ) {
                $shortcuts[] = array_filter([
                    'name' => $siteConfiguration["manifestShortcuts{$i}Name"] ?? '',
                    'short_name' => $siteConfiguration["manifestShortcuts{$i}ShortName"] ?? '',
                    'description' => $siteConfiguration["manifestShortcuts{$i}Description"] ?? '',
                    'url' => $contentObject->typoLink_URL(['parameter' => $siteConfiguration["manifestShortcuts{$i}Url"] ?? '', 'forceAbsoluteUrl' => true]),
                    'icons' => array_filter([
                        'src' => $this->getPathForSrc($siteConfiguration["manifestShortcuts{$i}IconSrc"] ?? ''),
                        'sizes' => $siteConfiguration["manifestShortcuts{$i}IconSizes"] ?? ''
                    ])
                ]);
            }
        }

        return $shortcuts;
    }

    /**
     * @return ExtensionConfiguration
     */
    protected function getExtensionConfiguration(): ExtensionConfiguration
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class);
    }
}
