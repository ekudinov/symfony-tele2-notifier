# symfony-tele2-notifier

Транспорт через tele2 (SMS-ТАРГЕТ) для компонента Symfony Notifier

.env
```ini
TELE2_DSN=tele2://login:password@default?from=AUTHOR
```

config/packages/notifier.yaml
```yaml
framework:
    notifier:
        texter_transports:
            tele2: '%env(TELE2_DSN)%'
```

config/services.yaml
```yaml
services:
    Axmor\Symfony\Component\Notifier\Bridge\Tele2\SmsTransportFactory:
        tags: [ texter.transport_factory ]
```
