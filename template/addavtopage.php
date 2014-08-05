<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Добавить объявление</title>
    </head>
    <body>
        <?php
        if (isset($_SESSION['sendForm'])) {
            
            $myinputs = filter_var_array($_SESSION['sendForm'], $this->args);

            echo 'При заполнении формы были допущены ошибки:<br>';
            
            foreach ($myinputs['error'] as $error) {
                echo ' - '.$error.';<br>'.PHP_EOL;
            }
            
        } else {

            $myinputs = filter_var_array(array(), $this->args);
            
        }
        
        ?>
        <h1>Добавить объявление:</h1>
        
        <form enctype="multipart/form-data" action="index.php" method="POST">
            <label for="brand">* Марка:</label>
            <input name="brand" type="text" size="50" value="<?php echo $myinputs['brand'];?>">
            <br><br>
            <label for="model">* Модель:</label>
            <input type="text" name="model" size="50" value="<?php echo $myinputs['model'];?>">
            <br><br>
            <label for="price">* Цена:</label>
            <input type="text" name="price" size="30" value="<?php echo $myinputs['price'];?>"> руб.
            <br><br>
            <label for="typeofcarbody">Тип кузова:</label>
            <input type="text" name="typeofcarbody" size="50" value="<?php echo $myinputs['typeofcarbody'];?>">
            <br><br>
            <label for="colors[]">* Цвета:</label>
            <select name="colors[]" multiple="multiple" size="10">
            <?php
                if (isset($ret)) {
                    while ($row = $ret->fetch())
                    {
                        //if($row->id)
                        printf("            <option value=\"%s\" %s>%s</option>".PHP_EOL,
                                $row->id, (in_array($row->id, $myinputs['colors']) ? "selected" : ""), $row->name);
                    }
                }
            ?>
            </select>
            (можно выбирать несколько: CTRL+click)
            <br><br>
            <label for="description">Описание:</label>
            <textarea name="description" rows="5" cols="50"><?php echo $myinputs['description'];?></textarea>
            <br><br>
            <label for="avtopicture">Фотография:</label>
            <input type="file" name="avtopicture" id="avtopicture"><br>
            Максимальный размер загружаемого файла: <?php echo ini_get('upload_max_filesize')?>
            <br><br>
            <input type="submit" name="Submit" value="Отправить" />

            <input type="hidden" name="action" value="addavto" />
        
        </form>    

        <br><br>
        
        Поля отмеченные "*" обяазтельны для заполнения<br><br>
        <div><a href="index.php">Перейти на главную страницу</a></div>

    </body>
</html>
