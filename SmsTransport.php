<?php

namespace Axmor\Symfony\Component\Notifier\Bridge\Tele2;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SmsTransport extends AbstractTransport
{
    public const HOST = 'https://target.tele2.ru/api/v2/send_message';

    private string $login;

    private string $password;

    private string $from;

    public function __construct(string $login, string $password, string $from, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->login = $login;
        $this->password = $password;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('tele2://%s?from=%s', $this->getEndpoint(), $this->from);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, get_debug_type($message)));
        }

        $phone = $message->getPhone();

        $fPhone = str_replace('+', '', $phone);

        $response = $this->client->request('POST', self::HOST,
            [
                'auth_basic' => [$this->login, $this->password],
                'json' => [
                    'msisdn' => $fPhone,
                    'shortcode' => $this->from,
                    'text' => $message->getSubject()
                ],

            ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new TransportException('Unable to send the SMS', $response);
        }

        $content = $response->getContent(false);
        $result = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (is_array($result) && isset($result['status']) && 'ok' === $result['status']) {

            $sentMessage = new SentMessage($message, (string) $this);
            $sentMessage->setMessageId($result['result']['uid']);

            return $sentMessage;
        }

        throw new TransportException('Unable to send the SMS: ' . $content, $response);
    }
}
