<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Главная страница</title>
    </head>
    <body>
        <div><a href="index.php?action=addavto">Добавить объявление</a><br><br></div>
        <?php

        //$new_name = strtolower(substr(md5(time()*mktime()*rand()), 0, 26));
        
        //echo $new_name;
        
        while ($row = $ret->fetch())
            {
                //print_r($row);
            
                printf('<div><a href="index.php?action=showavto&id=%s"><img src="%s" width=\'140\' height=\'100\'><br>%s %s</a><br>%s руб.<br>%s<br></div><hr>',
                        $row->id, $this->getPhotoName($row->photoname, avto::PhotoSizeMainPage), $row->brand, $row->model, $row->price, $row->shortDesc);
            }
        ?>
    </body>
</html>
