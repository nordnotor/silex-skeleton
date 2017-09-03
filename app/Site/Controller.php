<?php

namespace App\Site;

use App\Interfaces\ControllerMiddlewareInterface;
use App\Components\Crud\CoreController;
use App\Site\Middleware\SecurityMiddleware;

/**
 * Class Controller
 * @package App\Site
 */
class Controller extends CoreController implements ControllerMiddlewareInterface
{
    protected $layoutTemplate;
    protected $pathTemplate;

    public function getMiddlewares(): array
    {
        return [
            SecurityMiddleware::class,
        ];
    }

    /**
     * Controller constructor.
     * @param string $layoutTemplate
     * @param null|string $modelName
     * @param string $pathTemplate
     * @param string $security
     */
    public function __construct(string $layoutTemplate, $modelName = null, string $pathTemplate = '', string $security = 'session')
    {
        $this->layoutTemplate = $layoutTemplate;
        $this->pathTemplate = empty($pathTemplate) ? "widget/$this->modelName" : $pathTemplate;

        parent::__construct($security, $modelName, '');
    }
}
