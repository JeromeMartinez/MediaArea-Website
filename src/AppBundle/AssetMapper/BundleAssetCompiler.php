<?php

namespace AppBundle\AssetMapper;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\AssetMapper\Compiler\AssetCompilerInterface;
use Symfony\Component\AssetMapper\MappedAsset;
use Symfony\Component\Filesystem\Path;

/**
 * Serves several stylesheets or several scripts as a single file.
 *
 * A "*.bundle.css" or "*.bundle.js" file lists the assets it is made of, one per line:
 *
 *     /* @include "bundles/app/js/jquery.min.js" *\/
 *
 * Each line is replaced by the content of the asset.
 */
class BundleAssetCompiler implements AssetCompilerInterface
{
    private const INCLUDE_PATTERN = '{^[ \t]*/\* @include "([^"]+)" \*/[ \t]*$}m';
    private const SOURCE_MAP_PATTERN = '{^[ \t]*(?://|/\*)[#@] sourceMappingURL=\S+(?:[ \t]*\*/)?[ \t]*$}m';
    private const RELATIVE_URL_PATTERN = '{url\(\s*+(["\']?)(?!/|\#|%23|data:|https?:)([^"\')\s]++)\1\s*+\)}';

    public function supports(MappedAsset $asset): bool
    {
        return 1 === preg_match('/\.bundle\.(css|js)$/', $asset->sourcePath);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function compile(string $content, MappedAsset $asset, AssetMapperInterface $assetMapper): string
    {
        return preg_replace_callback(self::INCLUDE_PATTERN, function (array $matches) use ($asset, $assetMapper) {
            $included = $assetMapper->getAsset($matches[1]);
            if (null === $included) {
                throw new \RuntimeException(sprintf(
                    'Unable to find the asset "%s" included in "%s".',
                    $matches[1],
                    $asset->sourcePath
                ));
            }

            // The bundle changes when one of its assets changes
            $asset->addDependency($included);

            $includedContent = $included->content ?? file_get_contents($included->sourcePath);

            // The source map of the asset is not the one of the bundle
            $includedContent = preg_replace(self::SOURCE_MAP_PATTERN, '', $includedContent);

            if ('css' === $asset->publicExtension) {
                // The relative URLs are the ones from the directory of the asset
                $directory = \dirname($included->publicPath);
                $includedContent = preg_replace_callback(
                    self::RELATIVE_URL_PATTERN,
                    static fn (array $url) => 'url("'.Path::join($directory, $url[2]).'")',
                    $includedContent
                );
            }

            return $includedContent;
        }, $content);
    }
}
