<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface ModelSearchInterface extends ModelInterface
{
    /**
     * Search filter.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function search();
}