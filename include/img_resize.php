<?php
/**
 * Класс изменения размера изображения
 */
Class resize
{

    private $image;
    private $size;
    private $imageResized;

    function __construct($fileName)
    {

        $this->image = $this->openImage($fileName);

    }

    /** Функция открывает изображение в переменную типа image класса 
     * 
     * @param string $file - имя файла с полным путём
     * @return gdimage or false
     */
    private function openImage($file)
    {

        if (!file_exists($file)) {  
            return false;  
        }  
        $this->size = getimagesize($file);  

        if ($this->size === false) {  
            return false;  
        }  

        $format = strtolower(substr($this->size['mime'], strpos($this->size['mime'], '/') + 1));  
        $icfunc = 'imagecreatefrom'.$format;  
        if (!function_exists($icfunc)) {  
            return false;  
        }  
        
        $result = @$icfunc($file);
        
        return $result;

    }

    /** Фунцкция масштабирует исходное изображение без искажений с заливкой определнным цветом
     * 
     * @param int $width - ширина
     * @param int $height - высота
     * @param int $rgb - цвет заливки
     * @return boolean
     */
    public function resizeImage($width, $height, $rgb = 0xFFFFFF) {

        if ((!$this->image) || (!$this->size)) {
            return false;
        }
    
        $x_ratio = $width  / $this->size[0];  
        $y_ratio = $height / $this->size[1];  

        if ($height == 0) {  

            $y_ratio = $x_ratio;  
            $height  = $y_ratio * $this->size[1];  

        } elseif ($width == 0) {  

            $x_ratio = $y_ratio;  
            $width   = $x_ratio * $this->size[0];  

        }  

        $ratio       = min($x_ratio, $y_ratio);  
        $use_x_ratio = ($x_ratio == $ratio);  

        $new_width   = $use_x_ratio  ? $width  : floor($this->size[0] * $ratio);  
        $new_height  = !$use_x_ratio ? $height : floor($this->size[1] * $ratio);  
        $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width)   / 2);  
        $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);  

        $this->imageResized = imagecreatetruecolor($width, $height);  

        imagefill($this->imageResized, 0, 0, $rgb);  

        imagecopyresampled($this->imageResized, $this->image, $new_left, $new_top, 0, 0, $new_width, $new_height, $this->size[0], $this->size[1]);

        return true;  
        
    }
    /** Функция сохраняет измененное изображение в файл
     * 
     * @param string $file - файл с полным путём
     * @param int $quality - степень сжатия jpg
     * @return boolean
     */
    public function saveImage($file, $quality="100")
    {
        if ($this->imageResized) {
            if(imagejpeg($this->imageResized, $file, $quality)) {
                imagedestroy($this->imageResized);
                return true;
            }
        }
        return false;
    }
}

?>