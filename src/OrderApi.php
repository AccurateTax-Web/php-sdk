<?php
namespace AccurateTax;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class OrderApi {
    private $license;
    private $checksum;
    public $response;
    private $domain;
    public $statusCode;

    /**
     * Create a new AccurateTax OrderApi Object
     *
     * @param string $license
     * @param string $checksum
     * @param string $domain
     */
    function __construct($license, $checksum, $domain='us1.accuratetax.com') {
        $this->license = $license;
        $this->checksum = $checksum;
        $this->domain = $domain;
    }

    /**
     * Get the Order Details
     *
     * @param string $ordernum
     *
     * @return false|object
     */
    public function getOrder($ordernum) {

        $host = 'https://' . $this->domain . '/getOrderDetailsService.php/' . $ordernum;
        $client = new Client([
            'auth' => [trim($this->license), trim($this->checksum)],
        ]);

        try {
            $this->response = $client->request('GET', $host);
            $this->statusCode = $this->response->getStatusCode();
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $this->statusCode = $response->getStatusCode();
                if ($this->statusCode == 404) {
                    if ($e->hasResponse()) {
                        $res = $response;
                    }
                    return false;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('An unexpected error occurred: ' . $e->getMessage());
        }

        if (isset($this->statusCode) && $this->statusCode != 200 && $this->statusCode != 202 && $this->statusCode != 404) {
            if (isset($this->response) && !is_null($this->response)) {
                if (method_exists($this->response, 'getStatusCode')) {
                    throw new \Exception('Error: ' . $this->response->getStatusCode());
                }
            } else {
                throw new \Exception('Error: ' . $this->statusCode);
            }
        } else if ($this->statusCode == 404) {
            return false;
        } else if (!isset($this->statusCode)) {
            throw new \Exception('No status code set');
        }

        try {
            $json = json_decode($this->response->getBody());
        } catch(\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
        return $json;
    }

}
