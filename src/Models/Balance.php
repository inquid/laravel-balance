<?php

namespace Geow\Balance\Models;

use Geow\Balance\Concerns\BalanceInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Number;

class Balance extends Model implements BalanceInterface
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->guarded[] = $this->primaryKey;
        $this->table = config('balance.table');
    }

    protected function amountCurrency(): Attribute
    {
        return Attribute::make(
            get: fn () => Number::currency($this->amount / 100),
        );
    }

    public function balanceable(): MorphTo
    {
        return $this->morphTo();
    }
}
