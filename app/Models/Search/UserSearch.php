<?php

namespace App\Models\Search;

use App\Models\User;
use App\Traits\ModelSearch;
use App\Interfaces\ModelSearchInterface;

class UserSearch extends User implements ModelSearchInterface
{
    use ModelSearch;

    public $defaultValues = [];

    public function search()
    {
        return $this->queryFilter()
            ->addFilter('first_name', 'where', 'like', "%$this->first_name%")
            ->addFilter('middle_name', 'where', 'like', "%$this->middle_name%")
            ->addFilter('last_name', 'where', 'like', "%$this->last_name%")
            ->addFilter('email', 'where', 'like', "%$this->email%");
    }
}