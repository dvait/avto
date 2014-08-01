<?php

class SQL extends PDO {
    
    /** Параметры подключения к БД, для простоты представлены в виде констант в этом классе */
    const PARAM_host='localhost';
    const PARAM_port='3306';
    const PARAM_db_name='avto';
    const PARAM_user='root';
    const PARAM_db_pass='';    
    
    /** Конструктор класса - подключение к БД*/
    public function __construct() {
        parent::__construct('mysql:host='.SQL::PARAM_host.';port='.SQL::PARAM_port.';dbname='.SQL::PARAM_db_name.';charset=utf8',
                             SQL::PARAM_user, SQL::PARAM_db_pass, 
                             array(PDO::ATTR_PERSISTENT => true,                       // постоянное соединение
                                   PDO::ATTR_EMULATE_PREPARES => false,                // для отображения ошибок sql-запроса
                                   PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ,       // получаем объект по умолчанию
                                   PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"  // кодовая страница UTF-8
                                   )
                            );
    }
    
    /** Фунция безопасного выполнения запроса с параметрами и возврата результата
     * @param $query - sql запрос, параметры
     * @return $response - результат
     * 
     *  */ 
    public function query($query){ 
        $args = func_get_args();
        array_shift($args); 

        $reponse = parent::prepare($query);
        
        if (!$reponse) {
            echo "\nPDO::errorInfo():\n";
            print_r(parent::errorInfo());
        } else {
            $reponse->execute($args);
        }
        return $reponse;
    }
    
}

/** Главный класс проекта */
class avto extends SQL{
    
    /** Конструктор класса */
    public function __construct() {
        
        parent::__construct();
                
    }
    
    public function getMainPage() {
        
        $ret = $this->query("select id, brand, model, price, photoname, SUBSTR(description, 1, 250) as shortDesc from avto");

        // запрос выполнен
        if ($ret){
            
            include('template/mainpage.php');

        }
        
    }
    
}

$avto = new avto();
/** Установку атрибутов перенести в класс */
//$avto->setAttribute();
$avto->getMainPage();
