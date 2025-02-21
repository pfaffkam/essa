## Creating first aggregate

In this section we will create our first ES Aggregate.

I.  First - create a class that extends `ESAggregateRoot` abstract class.
Note that, the class should be *final* and have a constructor that calls parent constructor.
```php
final class User extends ESAggregateRoot
{
    public readonly string $name;
    public readonly string $email;
    
    public static function new(string $name, string $email, ?Identity $identity = null): self
    {
        $user = new self(
            $identity ?? Identity::new(),
        );
        
        $user->recordThat(
            new UserCreated($name, $email)
        );
        
        return $user;
    }
    
    public function onUserCreated(UserCreated $event): void
    {
        $this->name = $event->name;
        $this->email = $event->email;
    }
}
```

Note some important points about this aggregate:
- The class should be *final*.
- In most ways you should 
- We create factory method, which should be used to create new instances of that aggregate.

II. Also, we use our first event - `UserCreated`, but we don't have it yet. Let's create it.
```php
final readonly class UserCreated extends AbstractAggregateEvent
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}
```

III. Now we need to create some service which will be responsible for persistence - the **repository**. 
You can do it  by extending `AbstractAggregateRepository` class.
```php
final class UserRepository extends AbstractAggregateRepository {}
```


