<?php

namespace Geow\Balance\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Geow\Balance\BalanceServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Geow\Balance\Concerns\BalanceInterface;
use Geow\Balance\Traits\HasBalance;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Number;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Geow\\Balance\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            BalanceServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-balance_table.php.stub';
        $migration->up();
        */
    }

    // Add a test that ensures I can use any model configured in the config('balance.model') and implements the BalanceInterface
    public function testIcanUseAnyModelConfiguredInTheConfigBalanceModelAndImplementsTheBalanceInterface()
    {
        config()->set('balance.model', CustomBalance::class);
        $model = config('balance.model');

        $user = new User();
        $user->setCredit(2000);
        $this->assertEquals(2000, $user->credit);
        $user->increaseCredit(1000);
        $this->assertEquals(3000, $user->credit);
        $user->decreaseCredit(500);
        $this->assertEquals(2500, $user->credit);

        $this->assertEquals('$25.00', $user->creditCurrency);
    }
}

// Create a model that uses the HasBalance trait and test that it can increase, decrease, set and reset the balance
class User extends Model
{
    use HasBalance;
}


class CustomBalance extends Model implements BalanceInterface
{
    protected function amountCurrency(): Attribute
    {
        return Attribute::make(
            get: fn () => Number::currency($this->amount / 100, 'MXN'),
        );
    }

    public function balanceable(): MorphTo
    {
        return $this->morphTo();
    }
}
