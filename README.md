# Описание

Croco - аналог игры "Крокодил". В начале каждого раунда выбирается художник, которому необходимо изобразить определенное слово за установленный промежуток времени. Остальным игрокам для победы необходимо угадать изображенное слово.

# О проекте

Для минимизации задержки при рисовании использован Swoole WebSocket сервер, который обрабатывает все запросы с клиента. В качестве базы данных используется Redis.

Схема backend:
![alt text](https://i.imgur.com/hNtkxi2.png)

Установка Swoole с поддержкой SSL протокола:  
```git clone https://github.com/swoole/swoole-src.git```  
```cd swoole-src```  
```phpize```  
```./configure --enable-openssl```  
```make && make install```  

Запуск websocket сервера:  
```php Server.php```


# Стек

* Server
  * PHP7
  * Swoole framework
  * Redis + Phpredis
* Client
  * JavaScript + jQuery

