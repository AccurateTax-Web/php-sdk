<?php
namespace AccurateTax;

use GuzzleHttp\Client;

class OrderApi {
    private $license;
    private $checksum;
    public $response;
    private $domain;

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

        $this->response = $client->request('GET', $host, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($this->license . ":"  . $this->checksum),
            ]
        ]);

        $statusCode = $this->response->getStatusCode();
        if ($statusCode == 404) {
            return false;
        }

        if ($statusCode != 200 && $statusCode != 202) {
            throw new \Exception('Error: ' . $this->response->getStatusCode());
        }

        $resp = $this->response;
        try {
            $json = json_decode($resp->getBody());
        } catch(\Exception $e) {
            throw new \Exception('Error: ' . $e->getMessage());
        }
        return $json;
    }

}
