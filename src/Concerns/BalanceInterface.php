<?php

declare(strict_types=1);

namespace Geow\Balance\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface BalanceInterface
{
    public function balanceable(): MorphTo;
}
