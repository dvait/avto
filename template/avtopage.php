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

        // $ret = $this->dbh->query("SELECT a.*, c.name as color FROM avto as a 
        //                           left join avto_color as ac on (a.id = ac.avto_id)
        //                           left join color as c on (ac.color_id = c.id) where a.id=?", $id);
        
        // В интернетах пишут, что fetch в два раза быстрее, чем fetchAll,
        // а тут нам важна скорость, поэтому будем использовать его
        // http://phpforum.su/index.php?showtopic=66952
        while ($row = $ret->fetch())
        {
            $avtocolors .= $row['color'] . ", ";
            
            if (!$memrow) { $memrow = $row; }
            
        }
        
        if ($memrow) {

            $avtocolors = trim($avtocolors,", ");
            if ($avtocolors == "") {
                $avtocolors = "Не указаны";
            }
            
            printf('<div><img src="%s" width=\'720\' height=\'540\'><br>'.
                   'Авто (марка, модель): %s %s<br>Тип кузова: %s<br>'.
                    'Цвета: %s<br>Цена: %s руб.<br>Описание: %s<br></div>',
                    $this->checkPhoto($memrow['photoname'], avto::PhotoSizeAvtoPageWidth), 
                    $memrow['brand'], $memrow['model'], $memrow['typecarbody'], 
                    $avtocolors, $memrow['price'], nl2br($memrow['description']));
        } else {
            echo "Объявление удалено.";
        }
        
        ?>

        <br><br><div><a href="index.php">Перейти на главную страницу</a></div>

    </body>
</html>
