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

    /** Размер ширины фото авто на главной странце */
    const PhotoSizeMainPage = "140";

    /** Размер ширины фото авто на странце объявления */
    const PhotoSizeAvtoPage = "720";

    /** Имя каталога со всеми картинками */
    const PhotoMainDirName = "images";

    /** Имя каталога с шаблонами картинок*/
    const PhotoTemplateDirName = "templates";
    
    
    /** Конструктор класса */
    public function __construct() {
        
        parent::__construct();
                
    }
    
    /** Функция осуществляет проверку наличия фото и возвращает полное имя файла 
     * фотографии в зависимости от переданных параметров и наличия фото 
     * @param $photoName - имя фото в mysql базе, $size - размер изображения
     * @return полное имя файла с путём
     */
    public function checkPhoto($photoName, $size = avto::PhotoSizeMainPage) {
        
        // Проверка на правильность передачи второго аргумента
        if (($size != avto::PhotoSizeMainPage) && ($size != avto::PhotoSizeAvtoPage)) {
            $size = avto::PhotoSizeMainPage;
        }
       
        $photoPrefix = avto::PhotoMainDirName .'/size'.$size;
        $photoTemplatePrefix = avto::PhotoMainDirName.'/'.avto::PhotoTemplateDirName.'/size'.$size;
        
        // файл не загружен в базу
        if ($photoName == "") {
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
    
    /** Генерируем главную страницу 
    */
    public function getMainPage() {
        
        $ret = $this->query("select id, brand, model, price, photoname, SUBSTR(description, 1, 250) as shortDesc from avto");

        // запрос выполнен
        if ($ret){
            
            include('template/mainpage.php');

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

            $ret = $this->query("SELECT a.*, c.name as color FROM avto as a left join avto_color as ac on (a.id = ac.avto_id) left join color as c on (ac.color_id = c.id) where a.id=?", $id);
            
            // запрос выполнен
            if ($ret){
                
                include('template/avtopage.php');

            }
            
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
                $id = filter_input($input, 'id', FILTER_VALIDATE_INT, 
                                   array("options" => array("min_range" => 1)));
                $this->getAvtoPage($id);
                break;
            
            default : 
                $this->getMainPage(); 
                return;
        }
        
    }
    
}

$avto = new avto();
//$avto->getAvtoPage(1);
$avto->checkAction();
//$avto->getMainPage();
