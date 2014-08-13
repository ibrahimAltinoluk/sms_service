<?php


class Chrome extends APP
{
    /**
     * @ian
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

    public function  __construct($args)
    {
        parent::__construct($args);


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


    public function setMember($v)
    {
        $this->member = $v;
        return $this;
    }

    public function  getMember()
    {
        return $this->member;
    }


} 