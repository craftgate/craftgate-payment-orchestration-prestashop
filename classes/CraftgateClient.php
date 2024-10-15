<?php

require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/lib/craftgate/autoload.php';

class CraftgateClient
{
    private static ?CraftgateClient $instance = null;
    private \Craftgate\Craftgate $craftgate;


    public static function getInstance($api_key, $secret_key, $api_url): CraftgateClient
    {
        if (!self::$instance) {
            self::$instance = new CraftgateClient($api_key, $secret_key, $api_url);
        }
        return self::$instance;

    }

    private function __construct($api_key, $secret_key, $api_url)
    {
        $this->craftgate = new \Craftgate\Craftgate(array(
            'apiKey' => $api_key,
            'secretKey' => $secret_key,
            'baseUrl' => $api_url,
        ));
    }

    public function initCheckoutForm($request)
    {
        $response = $this->craftgate->payment()->initCheckoutPayment($request);
        return $this->buildResponse($response);
    }

    public function retrieveCheckoutFormResult($token)
    {
        $response = $this->craftgate->payment()->retrieveCheckoutPayment($token);
        return $this->buildResponse($response);
    }

    private function buildResponse($response)
    {
        $response_json = json_decode($response);
        return $response_json->data ?? $response_json;
    }
}