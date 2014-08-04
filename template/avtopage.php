<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Объявление №<?php echo $id;?></title>
    </head>
    <body>
        <?php

        $avtocolors = "";
        $memrow = null;
        
        while ($row = $ret->fetch())
        {
            $avtocolors .= $row->color . ", ";
            
            if (!$memrow) { $memrow = $row; }
            
        }
        
        if ($memrow) {

            $avtocolors = trim($avtocolors,", ");
            if ($avtocolors == "") {
                $avtocolors = "Не указаны";
            }
            
            printf('<div><img src="%s" width=\'720\' height=\'540\'><br>Авто (марка, модель): %s %s<br>Тип кузова: %s<br>Цвета: %s<br>Цена: %s руб.<br>Описание: %s<br></div>',
               $this->checkPhoto($memrow->photoname, avto::PhotoSizeAvtoPage), $memrow->brand, $memrow->model, $memrow->typecarbody, $avtocolors, $memrow->price, $memrow->description);
        } else {
            echo "Объявление удалено.";
        }
        
        ?>

        <br><br><div><a href="index.php">Перейти на главную страницу</a></div>

    </body>
</html>
