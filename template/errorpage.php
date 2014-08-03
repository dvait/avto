<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Ошибка</title>
    </head>
    <body>
        При работе возникла ошибка:<br>
        <?php

            $errorMessage = htmlspecialchars($errorMessage);
            echo $errorMessage;
        
        ?>
        <br>
        <div><a href="index.php">Перейти на главную страницу</a></div>

    </body>
</html>
