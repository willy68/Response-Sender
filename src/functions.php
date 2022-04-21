<?php

namespace Http\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Send an HTTP response
 *
 * @param ResponseInterface $response Response to send
 *
 * @return void
 */
function send(ResponseInterface $response)
{
    // headers have already been sent
    if (!headers_sent()) {
        $http_line = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        header($http_line, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
    }

    $stream = $response->getBody();

    if ($stream->isSeekable()) {
        $stream->rewind();
    }

    while (!$stream->eof()) {
        echo $stream->read(1024 * 8);
    }

    if (\function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
}
