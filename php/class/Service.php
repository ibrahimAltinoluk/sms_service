<?php
/**
 * Created by PhpStorm.
 * User: ibrahimaltinoluk
 * Date: 6.08.2014
 * Time: 23:29
 */
//TODO Auth

/**
 * SERVICE METHODS DEFINE
 */
define("REGISTER_MEMBER", "register_member");
define("REGISTER_C2DM", "register_c2dm");
define("SEND_SMS", "send_sms");
define("SYNC", "sync");
define("GET_CONTACTS", "get_contacts");
define("GET_DEVICES", "get_devices");
define("GET_SENT", "get_sent");
define("LOGOUT_DEVICE", "logout_device");


class Service extends APP
{

    private $current_member;

    public function  __construct($args)
    {
        parent::__construct($args);
        $this->current_member = $this->create("Member");

    }


    public function unclearRequest()
    {
        return $this->response(array(406, "json"),
            json_encode(array(
                "code" => 406, "error" => "Yetkisiz işlem"
            ))
        );


    }


    public function apply()
    {
        $post = $this->getPostAsObject();

        if ($post->method == REGISTER_MEMBER) {
            echo $this->registerMember();
        } else if ($post->method == REGISTER_C2DM) {
            echo $this->registerC2DM();
        } else if ($post->method == SEND_SMS) {
            echo $this->sendSms();
        } else if ($post->method == GET_SENT) {
            echo $this->getSent();
        } else if ($post->method == SYNC) {
            echo $this->sync();
        } else if ($post->method == GET_CONTACTS) {
            echo $this->getContacts();
        } else if ($post->method == LOGOUT_DEVICE) {
            echo $this->logOutDevice();
        } else {
            echo $this->unclearRequest();
        }

    }


    public function getSent()
    {
        $post = $this->getPostAsObject();
        $device = $this->create("Device");
        $notification = $this->create("Notification");


        $device = $device->setToken($post->token)->loadBy("token");
        if (!$device) return $this->unclearRequest();

        $notifications = $notification->setDevice($device->getId())->loadNotificationsByDevice();
        return $this->response(array("json"), json_encode($notifications));
    }


    public function sendSms()
    {
        $post = $this->getPost();
        $notification = $this->create("Notification");
        $this->current_member
            ->setToken($post["token"])
            ->loadByChromeToken();

        return $notification
            ->bind($post)
            ->setMember($this->current_member)
            ->sendSms();


    }


    public function getContacts()
    {
        $post = $this->getPostAsObject();
        $token = $post->token;
        $keyword = $post->keyword;

        $member = $this->current_member
            ->setToken($token)
            ->loadByChromeToken();

        if (!$member) return $this->unclearRequest();


        $json = $member->filterContacts($keyword);

        return $this->response(array("json"), $json);


    }

    public function sync()
    {
        $post = $this->getPost();
        $device = $this->create("Device");


        $device = $device
            ->bind($post)
            ->loadBy("token");


        if (!$device) return $this->response(array("json"), json_encode(array("success" => false, "message" => "No Devices Found")));;

        $this->current_member
            ->setToken($post["token"])
            ->loadByDeviceToken()
            ->addDevice($device);

        $device->bindContacts($post["data"]);

    }

    public function logOutDevice()
    {
        $post = $this->getPost();
        $device = $this->create("Device");


        $device = $device
            ->bind($post)
            ->loadBy("deviceId");


        if (!$device) return $this->response(array("json"), json_encode(array("success" => true, "message" => "Logged Out")));;

        $device->remove(function ($removed) {
            if ($removed)
                echo $this->response(array("json"), json_encode(array("success" => true, "message" => "Logged Out")));
        });
    }


    public function registerC2DM()
    {
        $post = $this->getPost();

        $device = $this->create("Device");
        $device = $device
            ->setDeviceId($post["deviceId"])
            ->loadBy("deviceId");

        if (!$device) return $this->response(array("json"), json_encode(array("success" => false, "message" => "No Device Found")));


        $device->bind($post)
            ->existsBy("deviceId", function ($exists) use ($device) {
                if (!$exists)
                    $device->save();
                else
                    $device->update(array("id" => $device->getId()));
            });
    }

    public function registerMember()
    {
        $post = $this->getPostAsObject();

        $member = $this->current_member;

        if ($post->from != "device" && $post->from != "chrome") {
            echo $this->unclearRequest();
            return;
        }


        $member
            ->bind($this->getPost())
            ->existsBy("fbid", function ($exists) use ($member, $post) {
                if (!$exists)
                    $member->save();

                $member->loadBy("fbid");


                if ($post->from == "device") {

                    $device = $member->create("Device");
                    $device->bind($member->getPost())
                        ->setMember($member->getId())
                        ->existsBy("deviceId", function ($exists) use ($device) {
                            if (!$exists)
                                $device->save();
                            else
                                $device->update(array("id" => $device->getId()));
                        });


                } else if ($post->from == "chrome") {
                    $chrome = $member->create("Chrome");
                    $chrome
                        ->setToken($post->token)
                        ->setMember($member->getId())
                        ->existsBy("token", function ($exists) use ($chrome) {
                            if (!$exists) $chrome->save();
                        });
                    $member->setChrome($chrome);


                    /**
                     * retured Devices
                     */
                    echo $member->response(array("json"), $member->loadDevices());


                }


            });

    }
}