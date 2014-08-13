<?php


class Member extends APP
{
    /**
     * @ian
     * @Table:id=id
     */
    public $id;
    /**
     * @ian
     * @Table:name=name
     */
    public $name;
    /**
     * @ian
     * @Table:fbid=fbid
     */
    public $fbid;
    /**
     * @ian
     * @avoid:insert
     * @Table:token=token
     */
    public $token;
    /**
     * @ian
     * @Table:email=email
     */
    public $email;


    public $chrome;

    public $deviceIds = array();
    public $devices = array();
    /**
     * @ian
     * @avoid:insert
     * @Table:deviceId=deviceId
     */
    public $deviceId;

    public function  __construct($args)
    {
        parent::__construct($args);


    }

    public function loadByDeviceToken()
    {
        $token = $this->getToken();
        $result = mysql_query("select * from Device,Member where Device.member=Member.id AND Device.token='{$token}'");
        if (mysql_num_rows($result) == 0) return false;
        $member = mysql_result($result, 0, "member");
        $this->setId($member)->loadBy("id");
        return $this;
    }

    public function loadByChromeToken()
    {
        $token = $this->getToken();
        $result = mysql_query("select * from Chrome,Member where Chrome.member=Member.id AND Chrome.token='{$token}'");

        if (mysql_num_rows($result) == 0) return false;
        $member = mysql_result($result, 0, "member");
        $this->setId($member)->loadBy("id");
        return $this;
    }

    public function filterContacts($keyword)
    {
        $keyword = $this->clean($keyword);
        $result = mysql_query("select * from Contact,Member,Device where Device.member=Member.id AND Contact.Device=Device.id AND
               ( Contact.phone like '%{$keyword}%' OR  Contact.title like '%{$keyword}%') AND Member.id=" . $this->getId());;


        $results = array();
        for ($i = 0; $i < mysql_num_rows($result); $i++) {
            $phone = mysql_result($result, $i, "phone");
            $title = mysql_result($result, $i, "title");
            $results[] = array("title" => $title, "phone" => $phone);
        }

        return json_encode(array("results" => $results));
    }

    public function loadDevices()
    {
        $result = mysql_query("select * from Member,Device where Device.member=Member.id AND Member.id=" . $this->getId());;


        $results = array();
        for ($i = 0; $i < mysql_num_rows($result); $i++) {
            $deviceId = mysql_result($result, $i, "deviceId");
            $title = mysql_result($result, $i, "title");
            $results[] = array("title" => $title, "deviceId" => $deviceId);
        }

        $this->setDevices($results);

        return json_encode(array("devices" => $results));
    }

    public function  setId($v)
    {
        $this->id = $v;
        return $this;
    }

    public function  getId()
    {
        return $this->id;
    }

    public function setName($v)
    {
        $this->name = $v;
        return $this;
    }

    public function  getName()
    {
        return $this->name;
    }

    public function setFbid($v)
    {
        $this->fbid = $v;
        return $this;
    }

    public function  getFbid()
    {
        return $this->fbid;
    }


    public function setToken($v)
    {
        $this->token = $v;
        return $this;
    }

    public function  getToken()
    {
        return $this->token;
    }


    public function setEmail($v)
    {
        $this->email = $v;
        return $this;
    }

    public function  getEmail()
    {
        return $this->email;
    }

    public function setChrome($v)
    {
        $this->chrome = $v;
        return $this;
    }

    public function  getChrome()
    {
        return $this->chrome;
    }


    public function setDeviceId($v)
    {
        $this->deviceId = $v;
        return $this;
    }

    public function  getDeviceId()
    {
        return $this->deviceId;
    }


    public function setDeviceIds($v = array())
    {
        $this->deviceIds = $v;
        return $this;
    }


    public function setDevices($v = array())
    {
        $this->devices = $v;
        return $this;
    }

    public function getDevices()
    {
        return $this->devices;
    }


    public function  addDevice($item)
    {
        $this->devices[] = $item;
        return $this;
    }

    public function  addDeviceIds($item)
    {
        $this->deviceIds[] = $item;
        return $this;
    }


    public function removeDeviceIds($item = "")
    {
        $this->deviceIds = array_diff($this->deviceIds, array($item));
        return $this;
    }
} 