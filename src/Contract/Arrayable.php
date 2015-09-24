<?php
namespace Haldayne\DataStructure\Contract;

interface Arrayable
{
    /**
     * Return a native PHP array representation of the object.
     */
    public function toArray();
}
