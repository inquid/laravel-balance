<?php

namespace Geow\Balance\Traits;

use Geow\Balance\Concerns\BalanceInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Number;

trait HasBalance
{
    protected string $currency = 'USD';

    public function credits(): MorphMany
    {
        return $this->morphMany(config('balance.model'), 'balanceable');
    }

    public function withCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    protected function credit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->credits()->sum('amount'),
        );
    }

    protected function creditCurrency(): Attribute
    {
        return Attribute::make(
            get: fn () => Number::currency($this->credits()->sum('amount') / 100, $this->currency),
        );
    }

    public function increaseCredit(int $amount, ?string $reason = null): BalanceInterface
    {
        return $this->createCredit($amount, $reason);
    }

    public function decreaseCredit(int $amount, ?string $reason = null): BalanceInterface
    {
        return $this->createCredit(-1 * abs($amount), $reason);
    }

    public function setCredit(int $amount, ?string $reason = null): BalanceInterface
    {
        return $this->createCredit($amount, $reason);
    }

    public function resetCredit(): void
    {
        $this->credits()->delete();
    }

    public function hasCredit(): bool
    {
        return $this->credit > 0;
    }

    protected function createCredit(int $amount, ?string $reason = null): Model|BalanceInterface
    {
        return $this->credits()->create([
            'amount' => $amount,
            'reason' => $reason,
        ]);
    }
}
