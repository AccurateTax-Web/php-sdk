<?php
    namespace AccurateTax;

    use GuzzleHttp\Pool;
    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\RequestException;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;

    class MultiTaxRequest {
        /**
         * @var array Array of tax Requests
         */
        private $taxRequests =  [];

        /**
         * @var int Max size of Tax Requests in each batch
         */
        private $maxRequests;

        /**
         * @var string The domain for the request
         */
        private $domain = 'us1.accuratetax.com';

        /**
         * @var string The endpoint for the request
         */
        private $endPoint = '/service.php';

        /**
         * @var Pool Guzzle Pool
         */
        private $pool;

        /**
         * @var Client Guzzle Client
         */
        private $client;

        /**
         * Create a new MultiTaxRequest
         *
         * @param string $domain
         * @param string $path
         */
        public function __construct($domain='', $path='', $maxRequests=10) {
            if (!empty($domain)) {
                $this->domain = $domain;
            }

            if (!empty($path)) {
                $this->endPoint = $path;
            }
            if (is_int($maxRequests) && $maxRequests > 0 && $maxRequests < 10) {
                $this->maxRequests = $maxRequests;
            } else {
                $this->maxRequests = 10;
            }
            $this->client = new Client();
        }

        public function addTaxRequest(\AccurateTax\TaxRequest $taxRequest) {
            array_push($this->taxRequests, $taxRequest);
        }

        public function getRequestCount(): int {
            return count($this->taxRequests);
        }

        public function getRequests(): array {
            return $this->taxRequests;
        }

        public function setDomain($domain) {
            $this->domain = $domain;
        }

        public function send(bool $returnResponse = false) {
            $results = [];
            $errors = [];
            $requests = function ($taxRequests) {
                $uri = 'https://' . $this->domain . $this->endPoint;
                foreach($taxRequests as $taxRequest) {
                    $body = $taxRequest->getXML();
                    yield new Request('POST', $uri, [
                        'x-internal-call' => '1',
                    ], $body);
                }
            };
            $prevErrorState = libxml_use_internal_errors(true);
            $pool = new Pool($this->client, $requests($this->taxRequests),[
                'concurrency' => $this->maxRequests,
                'fulfilled' => function (Response $response, $idx) use (&$results, &$errors) {
                    $hasParsingError = false;
                    $req = $this->taxRequests[$idx];
                    $state = $req->getOrder()->getState();
                    $respErrors = [];
                    $parsingError = '';
                    try {
                        $taxResponse = new \SimpleXMLElement($response->getBody());
                        $results[$state][] = $taxResponse;
                        $respErrors = libxml_get_errors();
                    } catch (\Exception $e) {
                        $hasParsingError = true;
                        $parsingError = $e->getMessage();
                        $errors[] = $parsingError;
                    }

                    if (count($respErrors) > 0) {
                        if (!isset($this->taxRequests[$idx]->errors)) {
                            $this->taxRequests[$idx]->errors = [];
                        }
                        foreach($respErrors as $error) {
                            $this->taxRequests[$idx]->errors[] = $error->message;
                            $errors[] = $error->message;
                        }
                        libxml_clear_errors();
                    } else if (!$hasParsingError) {
                        $this->taxRequests[$idx]->response = $taxResponse;
                    } else {
                        if (!isset($this->taxRequests[$idx]->errors)) {
                            $this->taxRequests[$idx]->errors = [];
                        }
                        $this->taxRequests[$idx]->errors[] = $parsingError;
                    }
                    if (isset($taxResponse->errors)) {
                        if (!isset($this->taxRequests[$idx]->errors)) {
                            $this->taxRequests[$idx]->errors = [];
                        }
                        foreach($taxResponse->errors->error as $err) {
                           $this->taxRequests[$idx]->errors[] = (string)$err;
                           $errors[] = (string)$err;
                        }
                    }
                },
                'rejected' => function (RequestException $reason, $idx) use(&$errors) {
                    $request = $reason->getRequest();
                    $this->taxRequests[$idx]->errors[] = $reason->getMessage();
                    $errors[] = $reason->getMessage();
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();

            libxml_use_internal_errors($prevErrorState);

            return [
                'results' => $results,
                'errors' => $errors
            ];
        }
    }