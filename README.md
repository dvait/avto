avto
====

Задание:

Нужно сделать небольшой каталог авто, с возможностью добавления автомобилей. 

Каталог будет состоять из следующих частей:
1. Главная страница, с возможностью перехода на 2-ю, 3-ю ... n-страницу.
2. Страница выбранного автомобиля.
3. Форма добавления автомобиля.

Для каждого автомобиля задаются следующие данные:

1. Марка * (текстовое поле).
2. Модель * (текстовое поле).
3. Цена * (текстовое поле).
4. Тип кузова (текстовое поле).
5. Цвета *. Должна быть возможность задавать несколько цветов для автомобиля. 
   Форму для добавления цветов делать не нужно. Достаточно, сделать таблицу с цветами.
6. Описание.
7. Фото. При добавлении фотографии, нужно делать 2 миниатюры. Одна из миниатюр 
   выводится на странице авто, другая на страницах каталога.

Пример.

1. Audi.
2. A5.
3. 1 729 230.
4. Купе.
5. Красный, белый, зеленый.
6. В отличном состоянии!!! Вложений абсолютно никаких не требует.

Главная страница каталога, представляет из себя список автомобилей, отображаемых
в табличной форме. На одной странице каталога, выводится 5 записей.

Каждая запись включает в себя: 
1. Фотографию авто (миниатюра).
2. Марку и модель.
3. Цену.
4. Первые 250 символов из описания.

При клике, на фото автомобиля или на марку и модель человек должен переходить 
на страницу выбранного авто. На странице авто выводится вся доступная 
информация. Размеры миниатюр по вашему усмотрению.

Желательно использовать только нативный PHP, без использования фреймворков. 
В решении задачи нужно использовать ООП, работа с базой данных должна
осуществляться с помощью PDO. Авторизация и регистрация не нужны. 
Дизайн не важен и не учитывается при проверке задания. 
Исходники нужно разместить на bitbucket или github.

--------------------------------------------------------------------------------

Комментарии по выполнению задания:

Используется тип таблиц MyISAM, а не InnoDB т.к. на сайте из этого задания
будет множество запросов на чтение в чем MyISAM выигрывает, а без транзакций и 
внешних ключей можно легко обойтись.

Проверку заполнения полей не делал, т.к. в задании этого явно не указано и
проверка заполнения осуществляется на PHP.

Проверка существования каталога и права на запись не производится для
улучшения производительности. Такая проверка обычно осуществляется в 
административной панели, которой в этом примере нет.

--------------------------------------------------------------------------------

SQL создания базы:

CREATE DATABASE IF NOT EXISTS `avto` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `avto`;

DROP TABLE IF EXISTS `avto`;
CREATE TABLE IF NOT EXISTS `avto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `price` varchar(30) NOT NULL,
  `typecarbody` varchar(50) DEFAULT NULL,
  `description` text,
  `photoname` varchar(18) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `avto_color`;
CREATE TABLE IF NOT EXISTS `avto_color` (
  `avto_id` int(11) NOT NULL,
  `color_id` int(3) NOT NULL,
  KEY `avto_id` (`avto_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `color`;
CREATE TABLE IF NOT EXISTS `color` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
