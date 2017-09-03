<?php

namespace App\Api\v1;

use App\Components\Crud\CoreController;
use App\Middleware\ErrorSubscriber;
use App\Middleware\LocaleMiddleware;
use App\Middleware\SiteSecurityMiddleware;

class Controller extends CoreController
{
    public function getMiddlewares(): array
    {
        return [
            ErrorSubscriber::class,
            LocaleMiddleware::class,
            SiteSecurityMiddleware::class,
        ];
    }

    /**
     * Renders a template.
     *
     * @param array $result
     * @param int $status
     * @return array
     * @internal param string $template The template name
     * @internal param array $data An array of parameters to pass to the template
     * @internal param string $ext
     */
    public function render(array $result = [], $status = 200): array
    {
        return ['', $status, $result];
    }
}
