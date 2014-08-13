<?php
/**
 * Created by PhpStorm.
 * User: ibrahimaltinoluk
 * Date: 6.08.2014
 * Time: 23:30
 */

class BASE
{

    /**
     * @ian
     * Genişletilmiş sınıfa ait method
     * hangi genişletilen sınıf tarafından çağrılıyor
     */
    private $owner;


    protected $CLASS_PATHS = array(
        "Service" => "class/Service.php",
        "Member" => "class/Member.php",
        "Device" => "class/Device.php",
        "Chrome" => "class/Chrome.php",
        "Notification" => "class/Notification.php",
        "APP" => "app.php",
        "MAP" => "app.php"
    );


    //Dışardan Method ile erişilebilir.
    private $ARGS;

    //Dışardan Erişilebilir
    protected $post, $request, $query;

    public function  __construct($args = null)
    {
        //parent::__construct($args);

        $this->ARGS = $args;

        $this->post = $_POST;
        $this->query = $_GET;
        $this->request = $_REQUEST;


        $this->setOwner(get_class($this));
        return $this;
    }

    protected function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    protected function  getOwner()
    {
        return $this->owner;
    }


    public function setArgs($args = null)
    {
        $this->ARGS = $args;
        return $this;
    }

    public function getArgs()
    {
        return $this->ARGS;
    }


    public function setPost($post)
    {
        return $this->post = $post;
    }

    public function  getPost()
    {
        return $this->post;
    }

    public function  getPostAsObject()
    {
        return (object)$this->post;
    }

    public function setQuery($query)
    {
        return $this->query = $query;
    }

    public function  getQuery()
    {
        return $this->query;
    }

    public function  getQueryAsObject()
    {
        return (object)$this->query;
    }

    /**
     * @param $class_name
     * @param null $args
     * @return bool
     * @throws Exception
     */
    public function create($class_name, $args = null)
    {
        try {
            if (!class_exists($class_name))
                require_once $this->CLASS_PATHS[$class_name];

            return new $class_name($args);
        } catch (Exception $e) {
            throw $e;
            return false;
        }
    }

}


class Notations extends BASE
{

    private $ians = array();


    public function  __construct($args = null)
    {
        parent::__construct($args);
        return $this;
    }


    public function getIans()
    {
        return $this->getIansFromClass($this->getOwner());
    }

    public function addIan($item)
    {
        $this->ians[] = $item;
        return $this;
    }

    public function getIansFromClass($class)
    {

        $class = $this->CLASS_PATHS[$class];

        $token = token_get_all(
            file_get_contents($class)
        );


        foreach ($token as $it => $em):
            foreach ($em as $e => $m):
                if (strpos($m, "@ian")) {
                    $this->addIan($m);
                }
            endforeach;
        endforeach;

        return $this->ians;
    }
}

class MAP extends Notations
{


    public function  __construct($args = null)
    {
        parent::__construct($args);
        return $this;
    }

    public function  clean($string)
    {
        return mysql_real_escape_string($string);
    }

    public function bind($data)
    {
        foreach ($data as $key => $value) {

            //eğer class üzerinde bu değişken tanımlanmışsa
            if (property_exists(get_class($this), $key)) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }


    public function getColumnPair()
    {
        $ians = $this->getIans();
        //        /@(avoid):insert/g
        // /@(Table):(.*)\=(.*)/g

        $avoid = array();
        $columns = array();
        $pairs = array();
        foreach ($ians as $ian) {
            $match = strpos(str_replace(" ", "", $ian), "@avoid:insert");

            $part1 = explode("Table:", $ian);
            $part2 = explode(PHP_EOL, trim($part1[1]));
            $defines = explode("=", $part2[0]);
            $var = $defines[0];
            $column = $defines[1];
            $pairs[$var] = $column;


            //Avoid için dizimiz hazır
            if ($match != "") {
                $avoid[] = $column;
            } else {
                $columns[] = $column;
            }
        }


        $columns = array_unique($columns);
        $avoid = array_unique($avoid);
        $pairs = array_unique($pairs);

        return array("columns" => $columns, "avoid" => $avoid, "pairs" => $pairs);

    }

    public function  readyColumnsForInsert()
    {
        $columnPair = $this->getColumnPair();
        $avoid = $columnPair["avoid"];
        $columns = $columnPair["columns"];

        $values = array();

        foreach ($columns as $col) {
            $values[] = "'" . str_replace("'", "\\'", $this->{$col}) . "'";
        }

        $columns_string = join(",", $columns);
        $values_string = join(",", $values);
        $table = $this->getOwner();
        $sql = "insert into {$table} ({$columns_string}) values ({$values_string})";


        return $sql;
    }

    public function  readyColumnsForUpdate()
    {
        $columnPair = $this->getColumnPair();
        $avoid = $columnPair["avoid"];
        $columns = $columnPair["columns"];
        $values = array();

        unset($columns["id"]);
        foreach ($columns as $col) {
            if (property_exists(get_class($this), $col)) {
                $values[] = $col . "='" . str_replace("'", "\\'", $this->{$col}) . "'";
            }
        }

        $values_string = join(",", $values);
        $table = $this->getOwner();
        $sql = "update  {$table} set {$values_string} ";


        return $sql;
    }

    public function  save()
    {

        $sql = $this->readyColumnsForInsert();
        mysql_query($sql);

        return $this;

    }

    public function  update($w)
    {
        $wh = array("true");
        foreach ($w as $k => $v) {
            $wh[] = $k . "='" . str_replace("'", "\\'", $v) . "'";
        }

        $w = " where " . join(" AND ", $wh);

        $sql = $this->readyColumnsForUpdate() . $w;
        mysql_query($sql);
        return $this;

    }

    public function  remove()
    {

        $table = $this->getOwner();
        $w = " where id=" . $this->getId();
        $sql = "delete from {$table} " . $w;

        mysql_query($sql);
        return $this;

    }


    public function existsBy($column, $callback)
    {
        $table = $this->getOwner();
        $value = $this->{$column};
        $result = mysql_query("select * from {$table} where {$column}='{$value}'");
        $count = mysql_num_rows($result);


        $callback($count > 0);
    }

    public function  loadBy($column)
    {

        $table = $this->getOwner();

        $value = $this->{$column};
        if ($value == null || $value == "") {
            return false;
        }

        $result = mysql_query("select * from {$table} where {$column}='{$value}'");

        if (mysql_num_rows($result) == 0) {
            return false;
        }

        $columnPair = $this->getColumnPair();

        for ($i = 0; $i < mysql_num_fields($result); $i++) {
            $key = mysql_field_name($result, $i);
            if (property_exists(get_class($this), $key)) {
                $match = array_search($key, $columnPair["pairs"]);
                $this->{$match} = mysql_result($result, 0, $key);
            }
        }

        return $this;

    }
}

class APP extends MAP
{


    //Dışardan Erişilemez
    private $headers = array(
        "406" => "HTTP/1.1 406 Not Acceptable",
        "200" => "HTTP/1.1 200 OK",
        "403" => "HTTP/1.1 403 Forbidden",
        "404" => "HTTP/1.1 404 Not Found",
        "json" => "Content-Type: application/json",
        "html" => "Content-Type: text/html"

    );


    public function  __construct($args = null)
    {
        parent::__construct($args);
        return $this;
    }


    public function response($header = array(), $output = "")
    {
        foreach ($header as $h) {
            if (!array_key_exists($h, $this->headers)) {
                header($h);
            } else {
                header($this->headers[$h]);
            }
        }

        return ($output);

    }


}


//Create APP

$app = new APP();
