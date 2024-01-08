<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    const Student = 0;

    const Teacher = 1;

    const Admin = 2;
}
