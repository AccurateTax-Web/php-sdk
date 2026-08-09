<?php
    namespace AccurateTax;
    use GuzzleHttp\Client;
    use GuzzleHttp\Pool;
    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;

    class MultiTaxRequest {
        /**
         * @var array Array of tax Requests
         */
        private $taxRequests = [];

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
         * @var float Connection timeout in seconds
         */
        private float $connectTimeout = 5.0;

        /**
         * @var float Total request timeout in seconds
         */
        private float $requestTimeout = 20.0;

        /**
         * Create a new MultiTaxRequest
         *
         * @param string $domain
         * @param string $path
         */
        public function __construct($domain='', $path='', $maxRequests=12, protected $headers=[]) {
            if (!empty($domain)) {
                $this->domain = $domain;
            }

            if (!empty($path)) {
                $this->endPoint = $path;
            }
            if (is_int($maxRequests) && $maxRequests > 0 && $maxRequests < 15) {
                $this->maxRequests = $maxRequests;
            } else {
                $this->maxRequests = 15;
            }
            $this->client = new Client([
                'connect_timeout' => $this->connectTimeout,
                'timeout' => $this->requestTimeout,
            ]);
        }

        public function addTaxRequest(TaxRequest $taxRequest) {
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

        public function setConnectTimeout(float $seconds): void {
            if ($seconds > 0) {
                $this->connectTimeout = $seconds;
                $this->client = new Client([
                    'connect_timeout' => $this->connectTimeout,
                    'timeout' => $this->requestTimeout,
                ]);
            }
        }

        public function setRequestTimeout(float $seconds): void {
            if ($seconds > 0) {
                $this->requestTimeout = $seconds;
                $this->client = new Client([
                    'connect_timeout' => $this->connectTimeout,
                    'timeout' => $this->requestTimeout,
                ]);
            }
        }

        public function send(bool $returnResponse = false) {
            $results = [];
            $errors = [];

            $requests = function ($taxRequests) {
                $uri = 'https://' . $this->domain . $this->endPoint;
                foreach($taxRequests as $taxRequest) {
                    $body = $taxRequest->getXML();
                    yield new Request('POST', $uri, $this->headers, $body);
                }
            };

            $prevErrorState = libxml_use_internal_errors(true);

            $waves = array_chunk($this->taxRequests, $this->maxRequests);
            $waveCount = count($waves);

            foreach ($waves as $waveIndex => $waveRequests) {
                $pool = new Pool($this->client, $requests($waveRequests),[
                    'concurrency' => count($waveRequests),
                    'connect_timeout' => $this->connectTimeout,
                    'timeout' => $this->requestTimeout,
                    'fulfilled' => function (Response $response, $idx) use (&$results, &$errors, $waveRequests) {
                        $hasParsingError = false;
                        $taxResponse = null;
                        $req = $waveRequests[$idx];
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
                            if (!isset($req->errors)) {
                                $req->errors = [];
                            }
                            foreach($respErrors as $error) {
                                $req->errors[] = $error->message;
                                $errors[] = $error->message;
                            }
                            libxml_clear_errors();
                        } else if (!$hasParsingError) {
                            $req->response = $taxResponse;
                        } else {
                            if (!isset($req->errors)) {
                                $req->errors = [];
                            }
                            $req->errors[] = $parsingError;
                        }
                        if (isset($taxResponse) && isset($taxResponse->errors)) {
                            if (!isset($req->errors)) {
                                $req->errors = [];
                            }
                            foreach($taxResponse->errors->error as $err) {
                                $req->errors[] = (string)$err;
                                $errors[] = (string)$err;
                            }
                        }
                    },
                    'rejected' => function (\Throwable $reason, $idx) use (&$errors, $waveRequests) {
                        $req = $waveRequests[$idx];
                        if (!isset($req->errors)) {
                            $req->errors = [];
                        }
                        $req->errors[] = $reason->getMessage();
                        $errors[] = $reason->getMessage();
                    },
                ]);

                $waveNumber = $waveIndex + 1;
                $waveStart = microtime(true);
                $promise = $pool->promise();
                $promise->wait();
                $waveDuration = microtime(true) - $waveStart;
                error_log(sprintf('[MultiTaxRequest] Wave %d/%d completed in %.3fs (%d request(s))', $waveNumber, $waveCount, $waveDuration, count($waveRequests)));
            }

            libxml_use_internal_errors($prevErrorState);

            return [
                'results' => $results,
                'errors' => $errors
            ];
        }
    }