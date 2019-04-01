# Установка

Переходим в DOCUMENT_ROOT

Выполняем:
```
composer require worksolutions/bitrix-reduce-migrations
```
и
```
composer run-script post-install-cmd -d bitrix/modules/rg.tauth
```

В результате получим:
1. файлы модуля загружены в директорию ``bitrix/modules/rg.tauth``
2. модуль зарегистрирован в системе
