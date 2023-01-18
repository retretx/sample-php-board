<?php

namespace Rrmode\Pihach;


interface RequestInterface {
    public function getAll(): mixed;

    public function getRouteParams(): array;
}



