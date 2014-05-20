Config class
=========

Configuration class to get parameteres defined in xml, json or php conf files.


Usage
--------------

config.json
```json
{
    'companyName': 'Test Company'
}
```

PHP usage
```php
echo 'My company is' . Config::get('companyName');
```
