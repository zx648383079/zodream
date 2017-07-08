# 配置目录


## 文件配置

```php
    APP_DIR.'/Service/config/config.php'
    APP_DIR.'/Service/config/'.APP_MODULE.'.php'
```

## 动态配置

```php
Config::set(k, v);
```

## 获取
```php
Config::get(k, default);
```

## 注意

    请不要在配置文件中读取配置，可能造成死循环或获取不到正确的值