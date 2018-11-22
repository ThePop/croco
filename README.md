# Описание

Croco — онлайн аналог игры "Крокодил", где одному игроку необходимо изобразить определенное слово, а остальным - его угадать.

# О проекте

Для минимизации задержки при рисовании использован Swoole WebSocket сервер, который обрабатывает все запросы с клиента. В качестве базы данных используется Redis.

Схема backend:
![alt text](https://i.imgur.com/hNtkxi2.png)

Запуск websocket сервера: 
```php Server.php```


# Стек

* Server
  * PHP7
  * Swoole framework
  * Redis + Phpredis
* Client
  * JavaScript + jQuery
  
# DEMO

Демонстрационная версия (для начала игры необходимо 2 человека, либо 2 открытые кладки):

https://croco.thepop.ru/
