<?php

class SQL extends PDO {
    
    /** Конструктор класса - подключение к БД*/
    public function __construct() {

        include 'config/pdo_config.php';
        
        parent::__construct('mysql:host='.$PARAM_host.';port='.$PARAM_port.';dbname='.$PARAM_db_name.';charset=utf8',
                             $PARAM_user, $PARAM_db_pass, 
                             array(PDO::ATTR_PERSISTENT => true,                       // постоянное соединение
                                   PDO::ATTR_EMULATE_PREPARES => false,                // для отображения ошибок sql-запроса
                                   PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,     // получаем массив по умолчанию - работает быстрее
                                   PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"  // кодовая страница UTF-8
                                   )
                            );
    }
    
    /** Фунция безопасного выполнения запроса с параметрами и возвратом результата
     * @param $query - sql запрос, параметры
     * @return array $response - результат
     * 
     */ 
    public function query($query){ 
        $args = func_get_args();
        array_shift($args); 

        $reponse = parent::prepare($query);
        
        if (!$reponse) {
            //echo "\nPDO::errorInfo():\n";
            //print_r(parent::errorInfo());
        } else {
            $reponse->execute($args);
        }
        return $reponse;
    }

     /** Фунция безопасного выполнения запроса с параметрами и возвратом результата
     * @param string $query - sql запрос, параметры
     * @param array $args - массив с параметрами
     * @return array $response
     */
    public function queryArgs($query, $args){ 

        $reponse = parent::prepare($query);
        
        if (!$reponse) {
//            echo "\nPDO::errorInfo():\n";
//            print_r(parent::errorInfo());
        } else {
            $reponse->execute($args);
        }
        return $reponse;
    }
    
    /** Генерируем страницу ошибки
     * @param $errorMessage - ошибка
     */
    public function getErrorPage() {
        $errorMessage = "Произошла ошибка при работе с базой данных.";
        include('template/errorpage.php');
        return false;
    }

    /** Функция возвращает массив id и имен всех цветов из базы или массив id всех цветов из базы
     *  в зависимости от параметра $onlyIds   В случае ошибки - false
     * @param boolean $onlyIds - получить только id
     * @return array|boolean
     */
    public function sqlGetColors($onlyIds = false) {
        
        if ($onlyIds) {
            // получаем список цветов из sql в массив
            $ret = $this->query("select id from color");
        } else {
            $ret = $this->query("select * from color");
        }

        if ($ret) {
            if ($onlyIds) {
                return $ret->fetchAll(PDO::FETCH_COLUMN, 0);
            } else {
                return $ret;
            }
        } else {
            return $this->getErrorPage();
        }
    }
    
    /** Функция производит добавление в базу нового объявления
     * 
     * @param array $myinputs - массив с значением полей объявления
     * @param string $savedFileName - имя файла картинки
     * @return boolean
     */
    public function sqlAddAvto($myinputs, $savedFileName) {
        
        $checkMaxLenFileds = array(
            'brand' => 50,
            'model' => 50,
            'price' => 30,
            'typecarbody' => 50,
            'description' => 5000
        );

        // Начинаем генерировать sql запрос добавления в базу

        $sql = "insert into avto set ";
        $sqlargs = array();

        foreach ($checkMaxLenFileds as $key => $value) {

            if (strlen($myinputs[$key]) > $value) {
                $myinputs[$key] = substr($myinputs[$key], 0, $value);
            }
            if ($myinputs[$key]) {
                $sql .= "$key=?, ";
                $sqlargs[] = $myinputs[$key];
            }
        }

        if ($savedFileName) {
            $sql .= "photoname=?";
            $sqlargs[] = $savedFileName;
        }

        $sql = trim($sql, ", ");

        $ret = $this->queryArgs($sql, $sqlargs);

        if ($ret){
           return $this->lastInsertId();
        } else {
            return $this->getErrorPage();
        }
    }
    
    /** Функция добавляет цвета по объявлению в таблицу цветов
     * 
     * @param array $colors - массив id цветов для добавления
     * @param string $avtoInsertId - id объявления
     * @return boolean
     */
    public function sqlAddColors($colors, $avtoInsertId){
        
        if (!$avtoInsertId) {
            return false;
        }

        // добавляем данные в таблицу соответствия цветов и объявлений
        foreach ($colors as $color) {
            $retAvtoColor = $this->query("insert into avto_color set avto_id=?, color_id=?", $avtoInsertId, $color);

            if (!$retAvtoColor) {
                return $this->getErrorPage();
            }
        }

        return $retAvtoColor;
            
    }

    /** Функция возвращает результат select'a для главной страницы
     * 
     * @param int $page - номер страницы
     * @param type $limit - количество объявлений на страницу
     * @return type
     */
    public function sqlSelectForMainPage($page, $limit) {
        
        $start = ($page - 1) * $limit;
        
        $ret = $this->query("select SQL_CALC_FOUND_ROWS id, brand, model, price, photoname, SUBSTR(description, 1, 250) as shortDesc from avto LIMIT ?, ?", $start, $limit);

        if ($ret) {
            return $ret;
        } else {
            return $this->getErrorPage();
        }
    }
    
    /** Функция возвращает количество записей в базе avto
     * 
     * @return int|boolean
     */
    public function sqlGetFoundRows() {
            // SQL_CALC_FOUND_ROWS и FOUND_ROWS() использованы с расчётом на будущее,
            // наверняка придётся использовать какие-то условия (WHERE) в запросе в дальнейшем, 
            // а такая конструкция работает быстрее чем
            // $rows = $this->dbh->query("select count(*) as rows from avto")->fetch()['rows'];
            $retrows = $this->query("select FOUND_ROWS() as rows");
            if ($retrows){
                $fetchret = $retrows->fetch();
                return $fetchret['rows'];
            } else {
                return $this->getErrorPage();
            }
    }
    
    /** Функция получения данных по объвлению из базы по id
     * 
     * @param int $id - id объявления
     * @return array|boolean
     */
    public function sqlGetSelectForAvtoPage($id){

        // хороший способ с GROUP_CONCAT работает только в mysql, но ведь база может быть не в mysql
        //$ret = $this->query("SELECT a.*, GROUP_CONCAT(c.name) as colors FROM avto as a join avto_color as ac on (a.id = ac.avto_id) join color as c on (ac.color_id = c.id) where a.id=?", $id);

        $ret = $this->query("SELECT a.*, c.name as color FROM avto as a left join avto_color as ac on (a.id = ac.avto_id) left join color as c on (ac.color_id = c.id) where a.id=?", $id);

        if ($ret) {
            return $ret;
        } else {
            return $this->getErrorPage();
        }
        
    }
    
}

?>