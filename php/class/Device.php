<?php


class Device extends APP
{
    /**
     * @ian
     * @avoid:insert
     * @Table:id=id
     */
    public $id;
    /**
     * @ian
     * @Table:member=member
     */
    public $member;

    /**
     * @ian
     * @Table:token=token
     */
    public $token;

    /**
     * @ian
     * @Table:title=title
     */
    public $title;
    /**
     * @ian
     * @Table:register=register
     */
    public $register;

    /**
     * @ian
     * @Table:deviceId=deviceId
     */
    public $deviceId;

    public $contacts = array();


    public function  __construct($args)
    {
        parent::__construct($args);


    }

    public function setContacts($v)
    {
        $this->contacts = $v;
        return $this;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function addContact($v)
    {
        $this->contacts[] = $v;
        return $this;
    }

    public function bindContacts($data)
    {
        $json = json_decode($data, true);
        $deviceId = $this->getId();


        foreach ($json["contacts"] as $contact) {
            $title = $contact["title"];
            $phone = $contact["phone"];

            $this->addContact(array(
                "phone" => $phone,
                "title" => $title
            ));
            mysql_query("INSERT INTO Contact (id,device, title, phone)
                        SELECT * FROM (SELECT '','{$deviceId}','{$title}', '{$phone}') AS tmp
                        WHERE NOT EXISTS (
                            SELECT phone FROM Contact WHERE phone = '{$phone}' and device ='{$deviceId}'
                        ) LIMIT 1;");

        }


    }

    public function loadContacts()
    {
        if (!$this->getDeviceId()) return false;

        $sql = "select * from Contact where deviceId=" . $this->getDeviceId();
        $result = mysql_query($sql);
        for ($i = 0; $i < mysql_num_rows($result); $i++) {
            $contact = array(
                "title" => mysql_result($result, $i, "title"),
                "phone" => mysql_result($result, $i, "phone")
            );
            $this->addContact($contact);
        }

        return $this->getContacts();
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


    public function  getId()
    {
        return $this->id;
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


    public function setTitle($v)
    {
        $this->title = $v;
        return $this;
    }

    public function  getTitle()
    {
        return $this->title;
    }


    public function setMember($v)
    {
        $this->member = $v;
        return $this;
    }

    public function  getMember()
    {
        return $this->member;
    }


    public function setRegister($v)
    {
        $this->register = $v;
        return $this;
    }

    public function  getRegister()
    {
        return $this->register;
    }


}