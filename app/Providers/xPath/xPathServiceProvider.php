<?php

namespace App\Providers\xPath;

use DOMXPath;
use DOMDocument;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

/**
 * Class xPathServiceProvider
 * @package e1\providers\xPath
 *
 * @property Application $app
 *
 * @REQUIRE:
 *          -
 *
 * @REGISTER:
 *
 * $app->register(new e1\providers\xPath\xPathServiceProvider());
 *
 * @USAGE:
 *
 * $xPath = $app['xPath.parse']($url);
 * $nodes = $xPath->query("/html/body/div[1]/div[2]/div[1]/div[2]/div/*"); # xPath
 *
 * foreach ($nodes as $i => $node) {
 *      if ($node->nodeName != 'div') {
 *         continue;
 *      }
 *  ...
 * }
 */
class xPathServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function boot(Application $app, array $config = [])
    {
        // TODO: Implement boot() method.
    }

    public function register(Container $app)
    {
        $this->app = $app;
        $app['xPath'] = $this;

        $app['xPath.DomDocument'] = $app->protect(function () {
            return $this->getDomDocument();
        });

        $app['xPath.DomXPath'] = $app->protect(function (DomDocument $domDocument) {
            return $this->getDomXPath($domDocument);
        });

        $app['xPath.parse'] = $app->protect(function (string $url) {

            libxml_use_internal_errors(true);

            $content = $this->getContent($url);

            $document = $this->getDomDocument();

            $document->loadHTML($content);

            return $this->getDomXPath($document);

        });
    }

    /**
     * @param string $url
     * @return bool|string
     */
    public function getContent(string $url)
    {
        $content = file_get_contents($url);

        foreach ($http_response_header as $c => $h) {
            if (stristr($h, 'content-encoding') and stristr($h, 'gzip')) {
                $content = gzinflate(substr($content, 10, -8));
            }
        }

        return $content;
    }

    /**
     * @param DOMDocument $domDocument
     * @return DOMXPath
     */
    public function getDomXPath(\DOMDocument $domDocument)
    {
        return new DomXPath($domDocument);
    }

    /**
     * @param string|null $version
     * @param string|null $encoding
     * @return DOMDocument
     */
    public function getDomDocument($version = null, $encoding = null)
    {
        return new DOMDocument($version, $encoding);
    }
}