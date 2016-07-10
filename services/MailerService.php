<?php

namespace app\services;

use yii\swiftmailer\Mailer;

/**
 * Class MailerService
 * @package app\services
 */
class MailerService
{
    /**
     * @var string
     */
    protected $from;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * MailerService constructor.
     *
     * @param Mailer $mailer
     * @param string $from
     */
    public function __construct(Mailer $mailer, $from)
    {
        $this->mailer = $mailer;
        $this->from = $from;
    }

    /**
     * @param string $email
     * @param array $products
     */
    public function orderNotification($email, array $products)
    {
        $html = '';
        foreach ($products as $product) {
            $html .= sprintf('<p>Товар: %s; Количество: %s</p>', $product['name'], $product['quantity']);
        }

        $this
            ->mailer
            ->compose()
            ->setFrom($this->from)
            ->setTo($email)
            ->setSubject('Уведомление о заказе')
            ->setHtmlBody($html)
            ->send();
    }
}
