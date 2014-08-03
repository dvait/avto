<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Главная страница</title>
    </head>
    <body>
        <?php

        
        if ($row = $ret->fetch())
            {

                printf('<div><img src="%s" width=\'720\' height=\'540\'><br>Авто (марка, модель): %s %s<br>Тип кузова: %s<br>Цена: %s руб.<br>Описание: %s<br></div>',
                        $this->getPhotoName($row->photoname, avto::PhotoSizeAvtoPage), $row->brand, $row->model, $row->typecarbody, $row->price, $row->description);
            }
        ?>
        <br><br><div><a href="index.php">Перейти на главную страницу</a></div>

    </body>
</html>
