<?php
namespace tsframe\module;

use tsframe\Config;
use tsframe\exception\CashException;
use tsframe\module\Logger;
use tsframe\module\user\Cash;
use tsframe\module\user\SingleUser;

class PayssionModule {
    /**
     * Описание метода платежа в панели управления для пользователя
     * @var string
     */
    public static $payDescription = 'via Payssion';

    /**
     * @var PayssionClient
     */
    private static $client;

    public static function getClient(): PayssionClient {
        if(!is_object(self::$client)){
            self::$client = new PayssionClient(
                Config::get('payssion.api_key'),
                Config::get('payssion.secret_key'),
                (intval(Config::get('payssion.production_mode')) == 1)
            );
        }

        return self::$client;
    }

    public static function getInputPaymentData(){
        $input = file_get_contents("php://input");

        // В официальных доках почему-то указано, что данные поступают в JSON, но в реальности приходят в формате url_string
        parse_str($input, $inputData);
        if(!is_array($inputData) || sizeof($inputData) == 0){
            $inputData = json_decode($input, true);
        }

        // Assign payment notification values to local variables
        $pm_id = $inputData['pm_id'];
        $amount = $inputData['amount'];
        $currency = $inputData['currency'];
        $order_id = $inputData['order_id'];
        $state = $inputData['state'];

        $api_key = Config::get('payssion.api_key');
        $secret_key = Config::get('payssion.secret_key');

        $check_array = array(
                $api_key,
                $pm_id,
                $amount,
                $currency,
                $order_id,
                $state,
                $secret_key
        );

        $check_msg = implode('|', $check_array);
        $check_sig = md5($check_msg);
        $notify_sig = $inputData['notify_sig'];
        if ($notify_sig == $check_sig) {
            if($state == 'completed'){
                Logger::cash()->debug('[Payssion] Input payment data (from callback): completed!', ['cash-provider' => 'payssion', 'type' => 'notify', 'input' => $input]);
                self::acceptPayment($inputData);
            } else {
                Logger::cash()->debug('[Payssion] Input payment data (from callback): uncompleted operation', ['cash-provider' => 'payssion', 'type' => 'notify', 'input' => $input]);
            }
        } else {
            Logger::cash()->error('[Payssion] Input payment data (from callback): invalid signature', ['cash-provider' => 'payssion', 'type' => 'error', 'input' => $input]);
        }
    }

    /**
     * @return string completed|pending|cancelled|failed
     */
    public static function acceptPayment(array $data): string {
        $tId = $data['transaction_id'];
        $orderId = $data['order_id'];
        $userId = Cash::decodePayId($orderId, true);
        $amount = $data['amount'];
        $currency = $data['currency'];
        $state = $data['state'];

        if($currency != Cash::getCurrency()) throw new CashException('acceptPayment error: invalid payment currency', 0, $data);
        
        if($state == 'completed'){
            $cash = Cash::ofUserId($userId);
            if(!$cash->isTransactionExists($orderId)){
                $description = 'Пополнение баланса через Payssion (transaction #'.$tId.')';
                $cash->add($amount, $description, $orderId);
            } else {
                Logger::cash()->notice('transaction # ' . $orderId . ' already accepted !' , ['cash-provider' => 'payssion', 'data' => $data]);
            }
            return $state;
        } 

        if($state == 'pending' || $state == 'cancelled'){
            return $state;
        }

        return 'failed';

        
    }

    /**
     * Получить информацию о платеже
     * @return array transaction, pm_id, amount, currency, order_id, state
     */
    public static function getPaymentDetails(string $orderId): array {
        $payssion = self::getClient();
        try {
            $response = $payssion->getDetails(['order_id' => $orderId]);
            Logger::cash()->debug('[Payssion] Input payment data by order: ' . $orderId, ['cash-provider' => 'payssion', 'response' => $response]);
        } catch (\Exception $e){
            throw new CashException('Payssion::getPaymentDetails error: ' . $e->getMessage(), 0, ['orderId' => $orderId]);
        }

        if ($payssion->isSuccess()) {
            return $response;
        } else {
            throw new CashException('Payssion::getPaymentDetails unsuccessfully operation', 0, ['orderId' => $orderId, 'response' => $response]);
        }
    }

    public static function createPayment(string $amount, string $paymentId, SingleUser $forUser): string {
        $payssion = self::getClient();
        try {
            $currency = Cash::getCurrency();
            $orderId = Cash::createPayId($forUser->get('id'));
            $description = 'Пополнение баланса пользователя ' . $forUser->get('login') . ' (ID:' . $forUser->get('id') . ') через Payssion / ' . $amount . ' ' . $currency . ' / ID платежа: ' . $orderId;
            $response = $payssion->create([
                'amount' => $amount,
                'currency' => $currency,
                'pm_id' => $paymentId,
                'description' => $description,
                'order_id' => $orderId
            ]);
        } catch (\Exception $e){
            throw new CashException('Payssion::createPayment error on create payment: ' . $e->getMessage(), 0, [
                'amount' => $amount,
                'paymentId' => $paymentId,
                'userId' => $forUser->get('id'),
                'orderId' => $orderId,
                'description' => $description,
            ]);
        }

        if ($payssion->isSuccess()) {
            //handle success
            $todo = $response['todo'];
            if ($todo) {
                $todo_list = explode('|', $todo);
                if (in_array("redirect", $todo_list)) {
                    //redirect the users to the redirect url or send the url by email
                    $paylink = $response['redirect_url'];
                    return $paylink;
                }
            } else {
                throw new CashException('Payssion::createPayment cannot find redirect url', 0, [
                    'amount' => $amount,
                    'paymentId' => $paymentId,
                    'userId' => $forUser->get('id'),
                    'orderId' => $orderId,
                    'description' => $description,
                    'response' => $response,
                ]);
            }
        } else {
            throw new CashException('Payssion::createPayment unsuccessfully operation', 0, [
                'amount' => $amount,
                'paymentId' => $paymentId,
                'userId' => $forUser->get('id'),
                'orderId' => $orderId,
                'description' => $description,
                'response' => $response
            ]);
        }
    }

    public static function isDev(): bool {
        return intval(Config::get('payssion.production_mode')) == 0;
    }

    public static function getPaymentTypes(): array {
        $types = Config::get('payssion.payment_types');
        $types = explode(',', $types);

        foreach ($types as $key => $value) {
            $item = trim(strtolower($value));
            $name = str_replace('_', ' ', ucfirst($item));
            $types[$key] = ['type' => $item, 'icon' => self::getPaymentIcon($item), 'name' => $name];
        }

        if(self::isDev()){
            $types[] = ['type' => 'payssion_test', 'icon' => self::getPaymentIcon('payssion_test'), 'name' => 'Test'];
        }

        return $types;
    }

    public static function getPaymentIcon(string $iconName): string {
        return ($iconName == 'payssion_test') 
            ? 'https://sandbox.payssion.com/static/img/logoadmin.png' 
            : 'https://raw.githubusercontent.com/payssion/payssion-payment-logos/master/' . $iconName . '.png';
    }
}