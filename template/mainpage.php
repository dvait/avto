<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Главная страница</title>
    </head>
    <body>
        <div><a href="index.php?action=getpageaddavto">Добавить объявление</a><br><br></div>
        <?php

            //$ret = $this->dbh->query("select id, brand, model, price, photoname, SUBSTR(description, 1, 250) as shortDesc from avto");

            while ($row = $ret->fetch()) {
                printf('<div><a href="index.php?action=showavto&id=%s"><img src="%s" width=\'140\' height=\'100\'><br>%s %s</a><br>%s руб.<br>%s<br></div><hr>',
                        $row['id'], $this->checkPhoto($row['photoname'], avto::PhotoSizeMainPageWidth), $row['brand'], $row['model'], $row['price'], $row['shortDesc']);
            }

        // $rows = $this->dbh->query("select FOUND_ROWS() as rows")->fetch()['rows'];

        $num_pages = ceil($rows / avto::MainRowsPerPageLimit);

        if ($num_pages > 0){
            echo "Страницы:";
        }
        
        for($page = 1; $page <= $num_pages; $page++){
            if ($page == $curPage) {
                print ("<b>$page</b> ");
            } else {
                printf("<a href=\"?action=getmainpage&page=%1\$s\">%1\$s</a> ", $page);
            }
        }
        ?>
        
    </body>
</html>
