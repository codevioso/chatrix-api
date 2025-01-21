<?php

namespace App\Enums;

enum RoomType: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case PROTECTED = 'protected';

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
