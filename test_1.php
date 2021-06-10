<?php
/*
найти и убить файлы от удаленных товаров (или от старого сайта),  которые лежат вместе с картинками товаров в одной директории на диске
Есть сайт на битриксе, в каталоге сто тысяч товаров
у каждого есть картинки две родные (PREVIEW_PICTURE и DETAIL_PICTURE) 
и дополнительно могут быть поля типа файл (в том числе множественные)
задача:  
вычистить все файлы, у которых нет никаких связей с элементами инфоблоков  (по факту обычно это файлы от удаленных товаров или от старого сайта)
1) нужно написать алгоритм как будешь решать
2) написать такое решение
Задачу можно реализовывать как standalone, так и используя Bitrix API 


Алгоритм
проходим по всем файлам в нужной папке и ищем их имена с табл. b_file 
если не находим, то удаляем файл

*/

 require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
  
 global $USER;
 if (!$USER->IsAdmin()) {
     echo "Авторизуйтесь как администратор.";
     return;
 }
 $time_start = microtime(true);
 echo '<br>';
 ///////////////////////////////////////////////////////////////////
  
 define("NO_KEEP_STATISTIC", true);
 define("NOT_CHECK_PERMISSIONS", true);
  
 //Целевая папка для поиска файлов
 $rootDirPath = $_SERVER['DOCUMENT_ROOT'] . "/upload/iblock";
 //$rootDirPath = $_SERVER['DOCUMENT_ROOT'] . "/upload/support"; // Чтобы удалить в модуле техподдержка надо раскомментировать
 //$rootDirPath = $_SERVER['DOCUMENT_ROOT'] . "/upload/support/not_image"; //Чтобы удалить из данной папки

	 // Получаем записи из таблицы b_file
	 $arFilesCache = array();
	 $result = $DB->Query('SELECT FILE_NAME, SUBDIR FROM b_file WHERE MODULE_ID = "iblock"'); //Тут меняем iblock на support, чтобы удалить в модуле техподдержка
	 while ($row = $result->Fetch()) {
	     $arFilesCache[$row['FILE_NAME']] = $row['SUBDIR'];
	 }
	  
	  
	 $hRootDir = opendir($rootDirPath);
	 $count = 0;
	 $contDir = 0;
	 $countFile = 0;
	 $i = 1;
	 $removeFile=0;
	 while (false !== ($subDirName = readdir($hRootDir))) {
	     if ($subDirName == '.' || $subDirName == '..') {
	         continue;
	     }
	     //Счётчик пройденых файлов
	     $filesCount = 0;
	     $subDirPath = "$rootDirPath/$subDirName"; //Путь до подкатегорий с файлами
	     $hSubDir = opendir($subDirPath);
	     
	     while (false !== ($fileName = readdir($hSubDir))) {
	         if ($fileName == '.' || $fileName == '..') {
	             continue;
	         }
	         $countFile++;
	  
	         if (array_key_exists($fileName, $arFilesCache)) { //Файл с диска есть в списке файлов базы - пропуск
	             $filesCount++;
	             continue;
	         }
	         $fullPath = "$subDirPath/$fileName"; // полный путь до файла
       
	             //Удаление файла
	             if (unlink($fullPath)) {
	                 $removeFile++;
	                 echo "Удалил: " . $fullPath . '<br>';
	             }
	
	         $i++;
	         $count++;
	         unset($fileName);
	     }
	     closedir($hSubDir);
	     //Удалить поддиректорию, если счётчик файлов пустой - т.е каталог пуст
	     if (!$filesCount) {
	         rmdir($subDirPath);
	     }
	     $contDir++;
	 }
	 if ($count < 1) {
	     echo 'Не нашёл данных для удаления<br>';
	 }

	 echo 'Всего файлов удалил: <strong>' . $removeFile . '</strong><br>';
	 echo 'Всего файлов в ' . $rootDirPath . ': <strong>' . $countFile . '</strong><br>';
	 echo 'Всего подкаталогов в ' . $rootDirPath . ': <strong>' . $contDir . '</strong><br>';
	 echo 'Всего записей в b_file: <strong>' . count($arFilesCache) . '</strong><br>';
	 closedir($hRootDir);
	  
	  
	 ////////////////////////////////////////////////////////////////////
	 echo '<br>';
	 $time_end = microtime(true);
	 $time = $time_end - $time_start;
	  
	 echo "Время выполнения $time секунд\n";
	 require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
