<?php


namespace Textme\SMS;


class SMS
{
    private static $_instance;
    private static $_sendSMSMessage;
    private static $_user;
    private static $_messages;
    private static $_source = '';
    private static $_response;


    private static function _execute(&$dataXML)
    {
        $xml = $dataXML->asXML();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://my.textme.co.il/api",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/xml"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        self::$_response = ['send' => $xml];

        if ($err) {
            self::$_response['error'] = $err;
        } else {
            self::$_response['response'] = $response;
        }
    }

    private static function _createTemplate(&$message, &$sms)
    {
        extract($message);
        if (preg_match_all("/{{(.*?)}}/", $template, $m)) {
            foreach ($m[1] as $i => $varname) {
                $varname = trim($varname);
                $template = str_replace($m[0][$i], $$varname, $template);
            }
        }
        $sms->addChild('message', $template);
    }

    private static function _setPhoneNumber(&$number, &$phones)
    {
        if (is_array($number) && !empty($number['phone']) && !empty($number['id'])) {
            $phones->addChild('phone', $number['phone'])->addAttribute('id', $number['id']);
        } else if (is_array($number) && !empty($number['phone'])) {
            $phones->addChild('phone', $number['phone']);
        } else {
            $phones->addChild('phone', $number);
        }
    }

    private static function _addSingelMessage(&$sms)
    {
        extract($sms);
        if (empty($source)) {
            self::addMessage($numbers, $message);
        } else {
            self::addMessage($numbers, $message, $source);
        }
    }

    private static function _addUserToXML(&$xml)
    {
        $user = $xml->addChild('user');
        $user->addChild('username', self::$_user['username']);
        $user->addChild('password', self::$_user['password']);
    }

    private static function _makeMassageToSend(&$source)
    {
        self::$_sendSMSMessage = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><bulk/>');
        self::_addUserToXML(self::$_sendSMSMessage);
        self::$_messages = self::$_sendSMSMessage->addChild('messages');
        self::$_source = $source;
    }

    /**
     * Init this system and create a instance of class
     * @param string $username The username of textme system.
     * @param string $password The password of textme system.
     * @param string string $source The Sender name or number.
     * @return SMS The instance of class.
     */
    public static function init($username, $password, $source = '')
    {
        return new self($username, $password, $source);
    }

    /**
     * Init this system and create a instance of class
     * @param string $username The username of textme system.
     * @param string $password The password of textme system.
     * @param string $source The Sender name or number.
     * @return SMS The instance of class.
     */
    public function __construct($username, $password, $source = '')
    {
        if (empty(self::$_sendSMSMessage)) {
            self::$_instance = $this;
            self::$_user = [
                'username' => $username,
                'password' => $password,

            ];
            self::_makeMassageToSend($source);

        }
        return self::$_instance;
    }

    /**
     * Add a singel template of massage and send to one or more phones
     * @param string|array $numbers The number or numbers of phones
     * @param string|array $message The message to send if its be array its mast contain a template key and by {{key}} can contain vars in template.
     * @param string $source The Sender name or number for this message (optional).
     * @return SMS The instance of class.
     */
    public static function addMessage($numbers, $message, $source = false)
    {
        $sms = self::$_messages->addChild('sms');
        if (!empty($source)) {
            $sms->addChild('source', $source);
        } else if (!empty(self::$_source)) {
            $sms->addChild('source', self::$_source);
        }

        $phones = $sms->addChild('destinations');
        if (is_array($numbers)) {
            if (empty($number['phone']) && empty($number['id'])) {
                foreach ($numbers as $number) {
                    self::_setPhoneNumber($number, $phones);
                }
            } else {
                self::_setPhoneNumber($numbers, $phones);
            }
        } else {
            self::_setPhoneNumber($numbers, $phones);
        }

        if (is_array($message) && !empty($message['template'])) {
            self::_createTemplate($message, $sms);
        } else {
            $sms->addChild('message', $message);
        }
        return self::$_instance;
    }

    /**
     * Add many messages can do this with one or many templates of massage
     * @param array $data The data for send message must contain keys numbers,message and optional source.
     * @return SMS The instance of class.
     */
    public static function addMessages($data)
    {
        if (is_array($data)) {
            if (!empty($data['numbers']) && !empty($data['message'])) {
                self::_addSingelMessage($data);
            } else {
                foreach ($data as $sms) {
                    self::_addSingelMessage($sms);
                }
            }

        }

        return self::$_instance;
    }

    /**
     * Send the sms message.
     * @return SMS The instance of class.
     */
    public static function send()
    {

        self::_execute(self::$_sendSMSMessage);

        unset(self::$_sendSMSMessage->messages);
        self::$_messages = self::$_sendSMSMessage->addChild('messages');

        return self::$_instance;
    }

    /**
     * Get the user sms balance
     * @return array The array with success (simpleXML) or error and send xml
     */
    public static function getBalance()
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><balance/>');
        self::_addUserToXML($xml);
        self::_execute($xml);
        return self::getResponse();
    }

    /**
     * Get the last response
     * @return array The array with success (simpleXML) or error and send xml
     */
    public static function getResponse()
    {
        return self::$_response;
    }

}
