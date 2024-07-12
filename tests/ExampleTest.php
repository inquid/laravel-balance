<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Geow\Balance\Concerns\BalanceInterface;
use Geow\Balance\Traits\HasBalance;
use Illuminate\Database\Eloquent\Casts\Attribute;

it('can test', function () {
    expect(true)->toBeTrue();
});

it ('can use any model configured in the config balance.model and implements the BalanceInterface', function () {
    config()->set('database.default', 'testing');

    // Run the test user migration
    $migration = include __DIR__.'/create_test_user_table.php.stub';
    $migration->up();

    // Run the balance migration
    $migration = include __DIR__.'/../database/migrations/create_balances_table.php.stub';
    $migration->up();

    config()->set('balance.model', CustomBalance::class);

    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@doe.com',
    ]);
    $user->setCredit(2000);
    $this->assertEquals(2000, $user->credit);
    $user->increaseCredit(1000);
    $this->assertEquals(3000, $user->credit);
    $user->decreaseCredit(500);
    $this->assertEquals(2500, $user->credit);

    $this->assertEquals('Amount in custom format: 25', $user->creditCurrency);
});


// Create a model that uses the HasBalance trait and test that it can increase, decrease, set and reset the balance
class User extends Model
{
    use HasBalance;

    protected $fillable = ['name', 'email'];
}


class CustomBalance extends Model implements BalanceInterface
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
        $amount = $this->amount / 100;
        return Attribute::make(
            get: fn () => "Amount in custom format: $amount",
        );
    }

    public function balanceable(): MorphTo
    {
        return $this->morphTo();
    }
}
