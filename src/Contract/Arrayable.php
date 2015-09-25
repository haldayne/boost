<?php
namespace Haldayne\Boost\Contract;

interface Arrayable
{
    /**
     * Return a native PHP array representation of the object.
     */
    public function toArray();
}
