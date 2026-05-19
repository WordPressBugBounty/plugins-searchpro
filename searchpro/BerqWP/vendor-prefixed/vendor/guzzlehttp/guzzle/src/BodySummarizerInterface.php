<?php

namespace BerqWP_Deps\GuzzleHttp;

use BerqWP_Deps\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}
