<?php
namespace Haldayne\DataStructure\Contract;

interface Jsonable
{
    /**
     * Return a JSON representation of the object.
     */
    public function toJson();
}
