<?php

namespace Axmor\Symfony\Component\Notifier\Bridge\Tele2;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class SmsTransportFactory extends AbstractTransportFactory
{
    /**
     * @return SmsTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('tele2' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'tele2', $this->getSupportedSchemes());
        }

        $login =  $this->getUser($dsn);
        $password = $this->getPassword($dsn);

        $from = $dsn->getOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new SmsTransport($login, $password, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['tele2'];
    }
}
