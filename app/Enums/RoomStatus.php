<?php

namespace App\Enums;

enum RoomStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
