<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class PaginationSetting extends Enum
{
    const PER_PAGE = 10;

    const ORDER_BY = 'id';

    const ORDER_DIRECTION = 'asc';
}
