<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Geow\Balance\Concerns\BalanceInterface;
use Geow\Balance\Traits\HasBalance;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Number;

it('can test', function () {
    expect(true)->toBeTrue();
});

it ('can use any model configured in the config balance.model and implements the BalanceInterface', function () {
    config()->set('database.default', 'testing');

    // Run the test user migration
    $migration = include __DIR__.'/create_test_user_table.php.stub';
    $migration->up();

    // Run the balance migration
    $migration = include __DIR__.'/create_balances_custom_table.php.stub';
    $migration->up();

    config()->set('balance.table', 'balances_custom');
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

    $this->assertEquals('$25.00', $user->creditCurrency);
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

    // before saving the model ensure the custom column is the name of the model that originated the balance
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->custom = get_class($model->balanceable);
        });
    }

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
