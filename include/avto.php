<?php

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
    
    /** Конструктор класса */
    public function __construct() {
        
        try {
            $this->dbh = new SQL();
        }
        catch (Exception $e) {
            $this->getErrorPage('Произошла ошибка при подключении к базе.');
            return false;
        }
        
        $this->checkAction();
    }

    /** Генерируем страницу ошибки
     * @param $errorMessage - ошибка
     */
    public function getErrorPage($errorMessage) {
        include('template/errorpage.php');
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

        $allColors = $this->dbh->sqlGetColors(true);
        
        if(!$allColors) { 
            return false;
        }
        
        // Если количество переданных цветов, больше, чем есть в sql, 
        // то даже проверять не будем их соответствие
        if (count($colors) > count($allColors)) {
            return false;
        }
        
        // Идём по массиву переданных цветов
        foreach ($colors as $value) {
        
            // Если цвета нет в sql - сразу выход
            if (!in_array($value, $allColors)){
                return false;
            }
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
            
            unset($resize);
            
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
            $error[] = "Ошибка при проверке цветов.";
        }
        
        if (empty($error)) {
            // Проверяем заполнение полей
            foreach ($this->checkEmptyFields as $key => $value) {

                if (empty($myinputs[$key])) {
                    $error[] = $value;
                }

            }
        }

        if (!empty($error)) {
            $_SESSION['sendForm'] = $myinputs;
            $_SESSION['sendForm']['error'] = $error;

            header('Location: index.php?action=getpageaddavto');
        } else {

            // Если передан файл с картинкой - делаем миниатюры
            $savedFileName = $this->resizeAndSaveImage($_FILES["avtopicture"]);
            
            $avtoId = $this->dbh->sqlAddAvto($myinputs, $savedFileName);

            // запрос успешно выполнен - добвляем в базу цвета
            if ($avtoId) {
                $ret2 = $this->dbh->sqlAddColors($myinputs['colors'], $avtoId);
                if (!$ret2) { return false; }
            } else {
                return false;
            }        
            
            unset($_SESSION['sendForm']);
            $this->getSuccessAddAvtoPage($avtoId);
            return true;
        }
    }
    
    /** Генерируем главную страницу 
     * @param integer $curPage - номер выводимой страницы
     * @return boolean
     */
    public function getMainPage($curPage = 1) {

        // переменная используется в include
        $ret = $this->dbh->sqlSelectForMainPage($curPage, avto::MainRowsPerPageLimit);
        
        if ($ret){
            $rows = $this->dbh->sqlGetFoundRows();
            include('template/mainpage.php');
        }        
    }

    
    /** Генерируем страницу объявления
     * @param $id - объявления
     */
    public function getAvtoPage($id) {
        
        // не передан id - возвращаем страницу с ошибкой
        if(!$id){
            $this->getErrorPage('Объявление не найдено!');
        } else {

            $ret = $this->dbh->sqlGetSelectForAvtoPage($id);
            if ($ret){
                include('template/avtopage.php');
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
        $ret = $this->dbh->sqlGetColors();
        if ($ret) {
            include('template/addavtopage.php');
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

?>