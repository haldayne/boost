<?php
namespace Haldayne\Boost\Contract;

interface Jsonable
{
    /**
     * Return a JSON representation of the object.
     */
    public function toJson();
}
