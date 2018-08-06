# Cast FormRequest

```bash
$ composer require localdisk/cast-formrequest
```

```php
<?php
class BaseRequest extends FormRequest
{
    // add use CastAttribute
    use CastAttribute;

    /**
     *  The attributes that should be cast to native types.
     *  
     * @var array 
     */
    protected $casts = [
        'id' => 'int',
    ];

}

```