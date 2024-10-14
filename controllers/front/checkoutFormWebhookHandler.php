<?php
require_once _PS_MODULE_DIR_ . 'craftgate_payment_orchestration/classes/CraftgateClient.php';


class Craftgate_Payment_OrchestrationCheckoutFormWebhookHandlerModuleFrontController extends ModuleFrontController
{

    private CraftgatePaymentService $craftgatePaymentService;

    public function __construct()
    {
        parent::__construct();
        $this->auth = false;
        $this->craftgatePaymentService = new CraftgatePaymentService($this->module);
    }

    public function postProcess(): void
    {
        $webhook_data = json_decode(file_get_contents('php://input'), true);
        if (!isset($webhook_data) || !$this->shouldProcessWebhookRequest($webhook_data)) {
            exit();
        }

        try {
            $this->craftgatePaymentService->handleCheckoutPaymentWebhookResult($webhook_data["token"]);
        } catch (Exception $e) {
            Tools::displayError($e);
            PrestaShopLogger::addLog("Unexpected error occurred while creating order" . serialize($e));
        }
    }

    private function shouldProcessWebhookRequest($webhook_data): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $event_type = $webhook_data['eventType'];
        $status = $webhook_data['status'];
        $checkout_token = $webhook_data['payloadId'];

        if ($event_type !== 'CHECKOUTFORM_AUTH' || $status !== "SUCCESS" || !isset($checkout_token)) {
            return false;
        }
        return true;
    }
}
