<?php
namespace AccurateTax;

use GuzzleHttp\Client;

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
        $client = new Client();

        try {
            $this->response = $client->request('GET', $host, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->license . ":"  . $this->checksum),
                ]
            ]);
            $this->statusCode = $this->response->getStatusCode();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->statusCode = $e->getCode();
            if ($this->statusCode === 404) {
                $res = $e->getResponse();
                return false;
            }
        }

        if ($this->statusCode != 200 && $this->statusCode != 202) {
            throw new \Exception('Error: ' . $this->response->getStatusCode());
        }

        try {
            $json = json_decode($this->response->getBody());
        } catch(\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
        return $json;
    }

}
