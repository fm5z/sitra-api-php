<?php

namespace Apidae\ApiClient\Exception;

use Exception;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class ApidaeException extends \Exception
{
    protected $request;
    protected $response;

    public function __construct(RequestException $e)
    {
        $this->request  = $e->getRequest();
        $this->response = $e->getResponse();
        $simpleMessage  = $e->getMessage();
        $code    = 0;

        if ($this->response) {
            try {
                $decodedJson = json_decode((string) $this->response->getBody(), true);
                if ($decodedJson && isset($decodedJson['errorType'])) {
                    $simpleMessage = $decodedJson['errorType'].' '.$decodedJson['message'];
                }
            } catch (\InvalidArgumentException $e) {
                // Not Json
            }

            $code = $this->response->getStatusCode();
        }

        $responseDescription = $this->response ? Psr7\str($this->response) : 'none';
        $requestDescription = $this->request ? Psr7\str($this->request) : 'none';

        $message = sprintf("%s

Request: %s

Response: %s

", $simpleMessage, $requestDescription, $responseDescription);

        parent::__construct($message, $code, $e);
    }
}
