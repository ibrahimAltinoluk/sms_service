<?php
/**
 * Created by PhpStorm.
 * User: ibrahimaltinoluk
 * Date: 6.08.2014
 * Time: 23:46
 */

class Notification extends APP
{

    public $member;

    /**
     * @ian
     * @Table:to=who
     */
    public $to;
    /**
     * @ian
     * @Table:number=number
     */
    public $number;
    /**
     * @ian
     * @Table:message=message
     */
    public $message;
    /**
     * @ian
     * @Table:device=device
     */
    public $device;
    public $notifications = array();


    public function  __construct($args)
    {
        parent::__construct($args);
    }


    public function loadNotificationsByDevice()
    {


        $results = mysql_query("select * from Notification,Device where Notification.device=Device.id AND Device.id=" . $this->getDevice());
        if (mysql_num_rows($results) == 0) {
            $this->setNotifications(array());
            return false;
        }

        for ($i = 0; $i < mysql_num_rows($results); $i++) {
            $message = mysql_result($results, $i, "message");
            $to = mysql_result($results, $i, "who");
            $number = mysql_result($results, $i, "number");
            $at = mysql_result($results, $i, "at");

            $this->addNotification(array(
                "message" => $message,
                "to" => $to,
                "number" => $number,
                "at" => $at
            ));

        }


        return $this->getNotifications();
    }

    public function addNotification($v)
    {
        $this->notifications[] = $v;
        return $this;
    }

    public function setNotifications($v = array())
    {
        $this->notifications = $v;
        return $this;
    }

    public function getNotifications()
    {
        return $this->notifications;
    }


    public function setMember($v)
    {
        $this->member = $v;
        return $this;
    }

    public function getMember()
    {
        return $this->member;
    }


    public function setTo($v)
    {
        $this->to = $v;
        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }


    public function setMessage($v)
    {
        $this->message = $v;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setDevice($v)
    {
        $this->device = $v;
        return $this;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function setNumber($v)
    {
        $this->number = $v;
        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function log()
    {
        $to = $this->getTo();
        $message = $this->getMessage();
        $number = $this->getNumber();
        $device = $this->getDevice();

        $this->save();
    }

    public function sendSms()
    {


        if ($this->getTo() == "" || $this->getDevice() == "") {
            return $this->returnData(false);
        }

        $table = mysql_query("select *,Device.id as device_id from Device,Member where  Member.id=Device.member AND Member.id='" . $this->getMember()->getId() . "' AND Device.deviceId='" . $this->getDevice() . "'");
        if (mysql_num_rows($table) <= 0) {
            return false;
        }

        $this->setDevice(mysql_result($table, 0, "device_id"));

        $registatoin_ids = array(mysql_result($table, 0, "register"));
        $url = 'https://android.googleapis.com/gcm/send';


        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => array(
                "to" => $this->getNumber(),
                'message' => $this->getMessage()
            )
        );


        $headers = array(
            'Authorization: key=' . C2DM_API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = $this->checkResultAsBoolen(curl_exec($ch));
        curl_close($ch);

        $this->log();


        return $this->returnData($result);
    }

    function  returnData($result)
    {
        if ($result)
            return json_encode(array("message" => "Successfull", "extention_data" => array("text" => "Ok", "color" => "#00ff00")));
        else
            return json_encode(array("message" => "Failed", "extention_data" => array("text" => "Fail", "color" => "#ff0000")));
    }

    function checkResultAsBoolen($data)
    {
        return !(strpos($data, "Error") > -1 || strpos($data, "Unauthorized") > -1);
    }


} 