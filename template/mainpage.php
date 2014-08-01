<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Главная страница</title>
    </head>
    <body>
        <div><a href="index.php?action=addavto">Добавить объявление</a><br><br></div>
        <?php
            while ($row = $ret->fetch())
            {
                //print_r($row);
                printf('<div><a href="index.php?action=showavto&id=%s">%s %s</a><br>%s руб.<br>%s<br></div>',
                        $row->id, $row->brand, $row->model, $row->price, $row->shortDesc);
            }
        ?>
    </body>
</html>
