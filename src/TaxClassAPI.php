<?php
namespace AccurateTax;

class TaxClassAPI {

    /**
     * @var string Domain to use for API requests
     */
    protected $domain;

    /**
     * @var string Protocol to use for API requests
     */
    protected $protocol;

    /**
     * Create TaxClassAPI object
     * @param string $protocol
     * @param string $domain
     */
    public function __construct($protocol='https', $domain='us1.accuratetax.com')
    {
        if (!empty($domain)) {
            $this->domain = $domain;
        } else {
            throw new \Exception('Domain is required and cannot be empty');
        }

        if (!empty($protocol) && ($protocol == 'https' || $protocol == 'http')) {
            $this->protocol = $protocol;
        } else {
            throw new \Exception('Protocol is required and cannot be empty and can be either http or https');
        }
    }
    public function checkForStoreTaxClass($storeId, $taxClass)
    {
        $url = $this->protocol . '://' . $this->domain . '/checkStoreTaxClass.php';
        $resp = $this->sendToHost($url, [
            'storeId'  => $storeId,
            'taxClass' => $taxClass,
        ]);
        if ($resp === false) {
            return false;
        } else {
            return $resp['result'];
        }
    }

    public function notifyMissingTaxClass($storeId, array $taxClasses, array $additionalParams = [])
    {
        $url = $this->protocol . '://' . $this->domain . '/notifyMissingTaxClass.php';

        $resp = $this->sendToHost($url, [
            'storeId'  => $storeId,
            'taxClasses' => $taxClasses,
            'additionalParams' => $additionalParams,
        ]);
        if ($resp === false) {
            return false;
        } else {
            return true;
        }
    }

    private function sendToHost($url, $data)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $headers = [
            'Content-Type: application/json',
            'X-AT: 1'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec( $ch );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == '404') {
            return false;
        } else {
            $response = trim( $response );
            if (empty($response)) {
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpcode == 200 || $httpcode == 202) {
                    return true;
                } else {
                    return false;
                }
            } else {
                $json = json_decode($response, true);

                return $json;
            }
        }
    }

}