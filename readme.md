# Cast FormRequest

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![StyleCI](https://github.styleci.io/repos/143679336/shield)](https://github.styleci.io/repos/143679336)
[![Build Status](https://img.shields.io/travis/localdisk/cast-formrequest/master.svg?style=flat-square)](https://travis-ci.org/localdisk/cast-formrequest)

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