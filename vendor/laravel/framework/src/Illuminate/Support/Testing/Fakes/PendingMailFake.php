<?php

namespace Illuminate\Support\Testing\Fakes;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\PendingMail;

class PendingMailFake extends PendingMail
{
    /**
     * Create a new instance.
     *
     * @param MailFake $mailer
     * @return void
     */
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send a new mailable message instance.
     *
     * @param Mailable $mailable ;
     * @return mixed
     */
    public function send(Mailable $mailable)
    {
        return $this->sendNow($mailable);
    }

    /**
     * Send a mailable message immediately.
     *
     * @param Mailable $mailable ;
     * @return mixed
     */
    public function sendNow(Mailable $mailable)
    {
        $this->mailer->send($this->fill($mailable));
    }

    /**
     * Push the given mailable onto the queue.
     *
     * @param Mailable $mailable ;
     * @return mixed
     */
    public function queue(Mailable $mailable)
    {
        return $this->mailer->queue($this->fill($mailable));
    }
}