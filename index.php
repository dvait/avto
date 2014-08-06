<?php

include('include/img_resize.php');


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
    
}

/** Главный класс проекта */
class avto {

    /** Database handle - объект PDO
     * @var PDO
     */
    public $dbh;
    
    /** Размер ширины фото авто на главной странце */
    const PhotoSizeMainPageWidth = '140';
    /** Размер высоты фото авто на главной странце */
    const PhotoSizeMainPageHeight = '100';

    /** Размер ширины фото авто на странце объявления */
    const PhotoSizeAvtoPageWidth = '720';
    /** Размер ширины фото авто на странце объявления */
    const PhotoSizeAvtoPageHeight = '540';

    /** Количество объявлений на главной странице */
    const MainRowsPerPageLimit = 5;
    
    /** Имя каталога со всеми картинками */
    const PhotoMainDirName = 'images';

    /** Имя каталога с шаблонами картинок*/
    const PhotoTemplateDirName = 'templates';
    
    /** Аргументы для filter_*_array
     *  Здесь перечисляются все передаваемые формой поля и 
     *  способы их фильтрации  */
    private $args = array(
        'error'           => array(
                                'filter' => \FILTER_SANITIZE_SPECIAL_CHARS,
                                'flags'  => \FILTER_REQUIRE_ARRAY,
                             ),
        'brand'           => \FILTER_SANITIZE_SPECIAL_CHARS,
        'model'           => \FILTER_SANITIZE_SPECIAL_CHARS,
        'price'           => array (
                                'filter' => \FILTER_SANITIZE_NUMBER_FLOAT,
                                'flags'  => \FILTER_FLAG_ALLOW_FRACTION),
        'typecarbody'   => \FILTER_SANITIZE_SPECIAL_CHARS,
        'colors'          => array(
                                'filter' => \FILTER_VALIDATE_INT,
                                'flags'  => \FILTER_REQUIRE_ARRAY,
                             ),
        'description'     => \FILTER_SANITIZE_STRING
    );
    
    /** Поля обязательные для заполнения, текст ошибки в случае пустого поля
     * 
     */
    
    private $checkEmptyFields = array(
        'brand' => 'Не заполнено обязательное поле \'Марка\'',
        'model' => 'Не заполнено обязательное поле \'Модель\'',
        'price' => 'Не заполнено обязательное поле \'Цена\'',
        'colors' => 'Не выбраны цвета'
    );
    
    /** Список полей добавляемых в базу и максимальная длина поля
     *
     * @var array 
     */
    private $checkMaxLenFileds = array(
        'brand' => 50,
        'model' => 50,
        'price' => 30,
        'typecarbody' => 50,
        'description' => 5000
    );
    
    /** Конструктор класса */
    public function __construct() {
        
        $this->dbh = new SQL();
        $this->checkAction();
        
    }
    
    /** Функция осуществляет проверку наличия фото и возвращает полное имя файла 
     * фотографии в зависимости от переданных параметров и наличия фото 
     * @param string $photoName - имя фото в mysql базе, $size - размер изображения
     * @return string полное_имя_файла с путём
     */
    public function checkPhoto($photoName, $size = avto::PhotoSizeMainPageWidth) {
        
        // Проверка на правильность передачи второго аргумента
        if (($size != avto::PhotoSizeMainPageWidth) && ($size != avto::PhotoSizeAvtoPageWidth)) {
            $size = avto::PhotoSizeMainPageWidth;
        }
       
        $photoPrefix = avto::PhotoMainDirName .'/size'.$size;
        $photoTemplatePrefix = avto::PhotoMainDirName.'/'.avto::PhotoTemplateDirName.'/size'.$size;
        
        // файл не загружен в базу
        if (empty($photoName)) {
            return $photoTemplatePrefix.'/notuploaded.png'; 
        }

        $fullFileName = $photoPrefix.'/'.$photoName;
        
        // проверка наличия и размера файла
        if(file_exists($fullFileName)){
            if (filesize($fullFileName) > 0) {
                return $fullFileName;
            }
        }
        
        return $photoTemplatePrefix.'/notfound.png'; 
        
    }
    /** Фунция проверяет, что переданные цвета есть в таблице цветов 
     * и их не больше, чем в таблице sql
     * 
     * @param array $colors - массив с цветами
     * @return boolean
     */
    private function checkColors($colors) {

        // получаем список цветов из sql в массив
        $ret = $this->dbh->query("select id from color");
        $result = $ret->fetchAll(PDO::FETCH_COLUMN, 0);

        // Если количество переданных цветов, больше, чем есть в sql, 
        // то даже проверять не будем их соответствие
        if (count($colors) > count($result)) {
            return false;
        }
        
        // Идём по массиву переданных цветов
        foreach ($colors as $value) {
        
            // Если цвета нет в sql - сразу выход
            if (!in_array($value, $result)){
                return false;
            }
            //array_key_exists
        }
        
        return true;
    }
    /** Функия осуществляет создание и сохранение двух миниатюр из переданного файла.
     * Проверка существования каталога и доступ на запись не осуществляется для
     * увеличения производительности, предполагается, что это делается на этапе установки
     * @param string $uploadedfile
     * @return string|boolean
     */
    private function resizeAndSaveImage($uploadedfile) {

        // файл загружен без ошибок и его размер больше 0
        if (($uploadedfile["error"] == 0) && ($uploadedfile["size"] > 0)){

            $resize = new resize($uploadedfile["tmp_name"]);
            
            $resize->resizeImage(avto::PhotoSizeMainPageWidth, avto::PhotoSizeMainPageHeight);
            
            $repeat = false;
            $count = 0;
            // Пробуем записать файл, такая конструкция выбранна потому, что в большинстве случаев всё будет нормально
            // и для экономии ресурсов проверка начинается только в случае ошибки
            do {
                $newFileName = mktime().rand(1000,9999).'.jpg';
                $fullFileNameMainPage = avto::PhotoMainDirName .'/size'.avto::PhotoSizeMainPageWidth.'/'.$newFileName;

                $res = $resize->saveImage($fullFileNameMainPage, 65);
                if (!$res) {
                    if(file_exists($fullFileNameMainPage)){
                        // пробуем 3 раза
                        if ($count++ < 4) {
                            $repeat = true;
                        } else {
                            $repeat = false;
                        }
                    } else {
                        $repeat = false;
                    }
                }
            } while ((!$res) && ($repeat));

            // удалось записать первый файл - записываем второй
            if ($res) {
                $fullFileNameAvtoPage = avto::PhotoMainDirName .'/size'.avto::PhotoSizeAvtoPageWidth.'/'.$newFileName;
                $resize->resizeImage(avto::PhotoSizeAvtoPageWidth, avto::PhotoSizeAvtoPageHeight);
                $res2 = $resize->saveImage($fullFileNameAvtoPage, 65);
            }
            
            // только если обе картинки не загрузились возвращаем false, иначе имя файла
            if ((!$res) && (!$res2)) {
                return false;
            } else {
                return $newFileName;
            }
        
        } else {    
            return false;
        }
    }
    
    /** Выполняем необходимые проверки и добавляем объявление в базу
     * 
     */
    public function addAvto() {

        $error = array();

        $myinputs = filter_input_array(INPUT_POST, $this->args);

        // Проверяем цвета на соответствие в базе
        
        if (!$this->checkColors($myinputs['colors'])) {
            $error[] = "Да вы, батенька, хакер!";
        }
        
        // Проверяем заполнение полей
        foreach ($this->checkEmptyFields as $key => $value) {

            if (empty($myinputs[$key])) {
                $error[] = $value;
            }

        }

        if (!empty($error)) {
            $_SESSION['sendForm'] = $myinputs;
            $_SESSION['sendForm']['error'] = $error;

            header('Location: index.php?action=getpageaddavto');
        } else {

            // Если передан файл с картинкой - делаем миниатюры
            $savedFileName = $this->resizeAndSaveImage($_FILES["avtopicture"]);
            
            // Начинаем генерировать sql запрос добавления в базу

            $sql = "insert into avto set ";
            $sqlargs = array();
            
            foreach ($this->checkMaxLenFileds as $key => $value) {
            
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
            
            $ret = $this->dbh->queryArgs($sql, $sqlargs);

            // запрос успешно выполнен
            if ($ret) {
                $lastInsertId = $this->dbh->lastInsertId();
                
                if ($lastInsertId) {
                    // добавляем данные в таблицу соответствия цветов и объявлений
                    foreach ($myinputs['colors'] as $color) {
                        $retAvtoColor = $this->dbh->query("insert into avto_color set avto_id=?, color_id=?", $lastInsertId, $color);
                    
                        if (!$retAvtoColor) {
                            $this->getErrorPage('Произошла ошибка при добавлении цветов авто!');
                            return false;
                        }
                    }
                }
                
            } else {
                $this->getErrorPage('Произошла ошибка. Объявление в базу не добавлено!');
                return false;
            }        
            
            unset($_SESSION['sendForm']);
            $this->getSuccessAddAvtoPage($lastInsertId);
            return true;
        }
    }
    
    /** Генерируем главную страницу 
     * @param integer $curPage - номер выводимой страницы
     * @return boolean
     */
    public function getMainPage($curPage = 1) {

        $start = ($curPage - 1) * avto::MainRowsPerPageLimit;
        
        $ret = $this->dbh->query("select SQL_CALC_FOUND_ROWS id, brand, model, price, photoname, SUBSTR(description, 1, 250) as shortDesc from avto LIMIT ?, ?", $start, avto::MainRowsPerPageLimit);
        
        // запрос выполнен
        if ($ret){
            // SQL_CALC_FOUND_ROWS и FOUND_ROWS() использованы с расчётом на будущее,
            // наверняка придётся использовать какие-то условия (WHERE) в запросе в дальнейшем, 
            // а такая конструкция работает быстрее чем
            // $rows = $this->dbh->query("select count(*) as rows from avto")->fetch()['rows'];
            $retrows = $this->dbh->query("select FOUND_ROWS() as rows");
            if ($retrows){
                $rows = $retrows->fetch()['rows'];
            } else {
                $this->getErrorPage('Произошла ошибка при работе с базой данных!');
                return false;
            }
            include('template/mainpage.php');
        } else {
            $this->getErrorPage('Произошла ошибка при работе с базой данных!');
            return false;
        }        
        
    }

    /** Генерируем страницу ошибки
     * @param $errorMessage - ошибка
     */
    public function getErrorPage($errorMessage) {
        include('template/errorpage.php');
    }
    
    
    /** Генерируем страницу объявления
     * @param $id - объявления
     */
    public function getAvtoPage($id) {
        
        // не передан id - возвращаем страницу с ошибкой
        if(!$id){
            $this->getErrorPage('Объявление не найдено!');
        } else {
            // хороший способ с GROUP_CONCAT работает только в mysql, но ведь база может быть не в mysql
            //$ret = $this->query("SELECT a.*, GROUP_CONCAT(c.name) as colors FROM avto as a join avto_color as ac on (a.id = ac.avto_id) join color as c on (ac.color_id = c.id) where a.id=?", $id);

            $ret = $this->dbh->query("SELECT a.*, c.name as color FROM avto as a left join avto_color as ac on (a.id = ac.avto_id) left join color as c on (ac.color_id = c.id) where a.id=?", $id);

            // запрос выполнен
            if ($ret){
                include('template/avtopage.php');
            } else {
                $this->getErrorPage("Ошибка при работе с базой данных.");
                return;
            } 

        }
        
    }

    /** Генерируем страницу успешного добавления объявления
     */
    public function getSuccessAddAvtoPage($id) {
        include('template/successaddavtopage.php');
    }
    /** Генерируем страницу добавления объявления
     */
    public function getAddAvtoPage() {

        // получаем список цветов из sql
        $ret = $this->dbh->query("select * from color");
        if ($ret) {
            include('template/addavtopage.php');
        } else {
            $this->getErrorPage("Ошибка при работе с базой данных.");
            return;
        }
    }
    
    /** Функция проверяет наличие команды в action и в зависимости от результатов запускает вывод нужной страницы
     *  По умолчанию или неверных параметрах выводится главная страница
     */
    public function checkAction() {
        
        $request_method = filter_input(\INPUT_SERVER, 'REQUEST_METHOD', \FILTER_SANITIZE_SPECIAL_CHARS);
        switch($request_method)
        {
            case 'GET': $input = INPUT_GET; break;
            case 'POST': $input = INPUT_POST; break;
            default : 
                $this->getMainPage(); 
                return;
        }
        
        $action = filter_input($input, 'action', \FILTER_SANITIZE_SPECIAL_CHARS);
        switch($action)
        {
            case 'showavto':  
                $id = filter_input($input, 'id', \FILTER_VALIDATE_INT, 
                                   array("options" => array("min_range" => 1)));
                $this->getAvtoPage($id);
                break;

            case 'getpageaddavto': 
                session_start(); 
                $this->getAddAvtoPage();
                break;

            case 'addavto': 
                session_start(); 
                $this->addAvto();
                break;
            
            case 'getmainpage':
                $page = filter_input($input, 'page', \FILTER_VALIDATE_INT, 
                                   array("options" => array("min_range" => 1)));
                $this->getMainPage($page); 
                break;
            
            default : 
                $this->getMainPage(); 
                return;
        }
        
    }
    
}

$avto = new avto();
