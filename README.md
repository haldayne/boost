# Upload Iterator

Install via composer: `php composer.phar require haldayne/data-structure *`

and use:

```php
use \Haldayne\DataStructure\Collection;

// a general purpose collection
$c = new Collection([ 'apple' => 'fruit', 'cabbage' => 'veggie' ]);
$c['banana'] = 'fruit';                          // array style
$c->                                             // or fluent style
    set('dill', 'herb')->
    set('eggplant', 'veggie')->
    each(function ($kind, $name) { echo $name; })
;

// advanced selecting
$c->partition(function ($kind, $name) { return $kind; });
var_dump($c->all())

/**
array (
    'fruit' => array (
        'apple' => 'fruit',
        'banana' => 'fruit',
    ),
    'veggie' => array (
        'cabbage' => 'veggie',
        'eggplant' => 'veggie',
    ),
    'herb' => array (
        'dill' => 'herb',
    )
)
```

