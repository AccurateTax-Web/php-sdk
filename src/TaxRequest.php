<?php
    namespace AccurateTax;

    use \AccurateTax\TaxRequest\Order;
    use GuzzleHttp\Client;

    class TaxRequest {
        /**
         * @var string The License Key to use for the request
         */
        private string $licensekey;

        /**
         * @var string The Checksum to use for the request
         */
        private string $checksum;

        /**
         * @var Order The Order to send to AccurateTax
         */
        private TaxRequest\Order $order;

        /**
         * @var bool Flag to commit the order
         */
        private bool $commit = false;

        /**
         * @var string The domain to send the request to
         */
        private string $domain = 'us1.accuratetax.com';

        /**
         * @var string The end point to send the request to
         */
        private string $endPoint = '/service.php';

        /**
         * @var \SimpleXMLElement The response from the request
         */
        public $response;

        /**
         * @var array Errors encountered during the request
         */
        public array $errors = [];

        /**
         * @var array meta data for the request
         */
        public array $meta = [];

        /**
         * Create a TaxRequest
         * @param string $licensekey
         * @param string $checksum
         */
        public function __construct(string $licensekey, string $checksum)
        {
            $this->licensekey = $licensekey;
            $this->checksum = $checksum;
        }

        /**
         * Get the domain
         *
         * @return string
         */
        public function getDomain(): string
        {
            return $this->domain;
        }

        /**
         * Get Order for Tax Request
         *
         * @return \AccurateTax\TaxRequest\Order
         */
        public function getOrder(): Order
        {
            return $this->order;
        }

        /**
         * Set Domain for Request
         *
         * @param string $domain
         * @return void
         */
        public function setDomain(string $domain): void
        {
            $this->domain = $domain;
        }

        /**
         * Set end point for Request
         *
         * @param string $endPoint
         * @return void
         */
        public function setEndPoint(string $endPoint): void
        {
            $this->endPoint = $endPoint;
        }

        /**
         * Set Commit flag for Request
         *
         * @param bool $commit
         * @return void
         */
        public function setCommit(bool $commit): void
        {
            $this->commit = $commit;
        }

        /**
         * Add an Order to the Tax Request
         *
         * @param Order $order
         * @return void
         */
        public function addOrder(Order $order): void
        {
            if (isset($this->order)) {
                throw New \Exception('Order Already assigned');
            }
            $this->order = $order;
        }

        /**
         * get Errors from Tax Request
         *
         * @return array
         */
        public function getErrors(): array
        {
            return $this->errors;
        }

        /**
         * Get XML for Tax Request
         *
         * @return string
         */
        public function getXml(): string {
            $xml = '<taxrequest>';
            $xml .= '<auth>';
            $xml .= '<licensekey>' . $this->licensekey . '</licensekey>';
            $xml .= '<checksum>' . $this->checksum . '</checksum>';
            $xml .= '</auth>';
            $xml .= '<breakdown>1</breakdown>';
            $xml .= '<item_breakdown>1</item_breakdown>';
            $xml .= '<commit>' . ($this->commit ? 'Y' : 'N') . '</commit>';
            $xml .= $this->order->getXml();
            $xml .= '</taxrequest>';

            return $xml;
        }

        /**
         * Send Tax Request to AccuratTax
         *
         * @param bool $returnResponse
         *
         * @return array|bool
         */
        public function send(bool $returnResponse = true): array|bool {
            $client = new Client(
                [
                    'timeout' => 60
                ]
            );
            $response = $client->request('GET', 'https://' . $this->domain . '/' . $this->endPoint, [
                'config' => [
                    'curl' => [
                        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                        CURLOPT_USERPWD => $this->licensekey . ':' . $this->checksum
                    ]
                ],
                'form_params' => [
                    'data' => $this->getXml()
                ]
            ]);

            try {
                $prevErrorState = libxml_use_internal_errors(true);;
                $taxResponse = simplexml_load_string(trim($response->getBody()));
                $errors = libxml_get_errors();
                if ( count( $errors ) > 0 ) {
                    foreach( $errors as $error ) {
                        array_push($this->errors, $error->message);
                    }
                    libxml_clear_errors();
                    libxml_use_internal_errors($prevErrorState);
                    if (!$returnResponse) {
                        return false;
                    } else {
                        return array(
                            'response' => $taxResponse,
                            'errors' => $this->errors
                        );
                    }
                } else {
                    $this->response = $taxResponse;
                }

                if (isset($taxResponse->errors)) {
                    foreach($taxResponse->errors->error as $err) {
                        array_push($this->errors, (string)$err);
                    }
                }

                if (!$returnResponse) {
                    if (count($this->errors) > 0) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return array(
                        'response' => $taxResponse,
                        'errors' => $this->errors
                    );
                }
            } catch( \Exception $e ){
                array_push($this->errors, $e->getMessage());
                if (!$returnResponse) {
                    return false;
                } else {
                    return array(
                        'errors' => $this->errors
                    );
                }
            }
        }
    }