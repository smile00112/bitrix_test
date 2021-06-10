<?php
/*
https://docs.google.com/spreadsheets/d/1AuTBQzV7P1c82XhJkynuQj4-xbKWhlGJ5J3BkG40U4w/edit?usp=drivesdk

Нужно взять chart.json и написать скрипт который на выходе будет отдавать chart_result.json  
https://drive.google.com/drive/folders/16tDtdgO9tw41ORuhO6tVazPUKS7TxeDP (тут файлы)

Это данные для Google Chart
задача заменить начальные значения 100 на null в контексте колонки (пояснения в google docs)
например было
100,100,100,100,100,100,89,101,102,100,100,100,100,100
стало
null,null,null,null,null,null,89,101,102,100,100,100,100,100

при этом 100 может повторяться не более трех раз
три раза подряд и потом значение отличное от 100 - нормально
если 4 подряд - это однозначно данные для зануления

размерность строки JSON может меняться 
первое значение в строке не учитываем, т.к. это метка времени Unix
корректный результат работы скрипта лежит в chart_result.json

Пояснения, что требуется в виде было-стало:  
https://docs.google.com/spreadsheets/d/1AuTBQzV7P1c82XhJkynuQj4-xbKWhlGJ5J3BkG40U4w/edit#gid=0


*/

$text = file_get_contents('https://drive.google.com/uc?export=download&id=10J3kpL7JjVqjs8iRWXiyeNCc0bPvT-VM');
//echo $text;

$data = json_decode($text, true);
/* соток в json всего две, так что зададим свой массив) */
$data = [
	[ 11111111, 2 , 3 , 100 ],
	[ 11111111, 2 , 3 , 100 ],
	[ 11111111, 100 , 3 , 100 ],
	[ 11111111, 100 , 3 , 101 ],
	[ 11111111, 100 , 3 , 4 ],
	[ 11111111, 100 , 3 , 100 ],
	[ 11111111, 100 , 3 , 100 ],
	[ 11111111, 1 , 3 , 100 ],
	[ 11111111, 100 , 3 , 100 ],

];

for( $i = 1; $i < count($data[0]); $i++ ){
	//переводим столбец в строку
	 $column_to_string = implode( '_', array_column($data, $i)).'_';

	//ищем вхождение 4 и более 100
	preg_match('/(100_){4,}/', $column_to_string, $matches);

	//если нашли, заменяем на null
	$column_to_string = str_replace($matches[0], $null_str, $column_to_string);
	if(!empty($matches)){
		$null_str = str_replace('100', 'null', $matches[0]);
		$result =  explode( '_', $column_to_string);

		//Заменяем значения в массиве
		foreach($data as $index => &$d){
			$d[$i] = $result[$index];
		}
	}
} 


echo json_encode($data);