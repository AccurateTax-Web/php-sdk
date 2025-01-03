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
                'Authorization: Basic' => $this->license . ":"  . $this->checksum,
            ]
        ]);


        if ($this->response->getStatusCode()!= 200) {
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
