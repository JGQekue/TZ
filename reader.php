<?php
if(isset($_FILES)) {

    $tim = $_POST['time'];
    $levelA = $_POST['aclevel'];
    if (empty($levelA) || empty($tim)){
        echo "Введите значения","<br>";
    }else{
        $allowedTypes = array("text/plain");
        $uploadDir = "files/";
        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            $filename = $_FILES['file']['name'][$i];
            if(file_exists("files/$filename")==true){
                echo "Файл с таким именем уже существует","<br>";
            }else {
                $uploadFile[$i] = $uploadDir . basename($_FILES['file']['name'][$i]);
                $fileChecked[$i] = false;
                echo $_FILES['file']['name'][$i] . " | " . $_FILES['file']['type'][$i] . " — ";

                for ($j = 0; $j < count($allowedTypes); $j++) {

                    if ($_FILES['file']['type'][$i] == $allowedTypes[$j]) {
                        $fileChecked[$i] = true;
                        break;
                    }
                }
                if ($fileChecked[$i]) {
                    if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $uploadFile[$i])) {
                        echo "Успешно загружен <br>";
                    } else {
                        echo "Ошибка " . $_FILES['file']['error'][$i] . "<br>";
                    }
                }
            }
        }

        //$fp = fopen("files/$filename", 'rt');
        //if ($fp) echo fpassthru($fp);
        //else echo "Ошибка при открытии файла";
        //$fp = fopen("http://www.yandex.ru", "r"); Для прямого чтения через http соединение
        touch("files/ch$filename");
        if (filesize("files/$filename") > 500000000){
            //$handle1 = fopen("files/$filename", "r");
            //$handle2 = fopen("files/ch$filename", "w");


        }else {
            $file_array = file("files/$filename");
            $new_array = array();
            for ($j = 0; $j < count($file_array); $j++) {
                if ($file_array[$j] != 0) {
                    array_push($new_array, $file_array[$j]);
                }
            }
            $new_array = array();
            for ($j = 0; $j < count($file_array); $j++) {
                if ($file_array[$j] != 0) {
                    array_push($new_array, $file_array[$j]);
                }
            }
            //print_r($new_array);
            logs($new_array, $tim, $levelA, $filename);
            $memory = memory_get_usage();
            if ($memory > 536870912){
                echo "Превышен лимит памяти";
            }
        }
    }
}else {
    echo "Вы не прислали файл!","<br>" ;
}
function logs($arr, $tim, $levelA, $filename){
    $x = 0;
    $counter = 0;
    $counterP = 0;
    $start = '';
    $end = '';
    $mass = 0;
    $fd = fopen("files/ch$filename", 'w') or die("не удалось создать файл");
    for($j = 1; $j <= count($arr); $j++) {
        $place = explode(" ", $arr[$j-1]);
        $place1 = explode(" ", $arr[$j]);
        $timeS = substr($place[3], -8, 5);
        $timeE = substr($place1[3], -8, 5);
        if ($arr[$j] != '') {
            if ($timeS == $timeE) {
                $mass++;
                if ((double)$place[8] >= 500 || (double)$place[10] > $tim) {
                    $counter++;
                }
            }else{
                $mass = 1;
                if ((double)$place[8] >= 500 || (double)$place[10] > $tim) {
                    $counter = 1;
                }
            }
                $result = 100 - $counter / $mass;
                if ($result < $levelA && $x == 0) {
                    $start = substr($place[3], -8);
                    $end = substr($place[3], -8);
                    $x = 1;
                    $counterP = $counter;
                    $counter = 0;
                } elseif ($result < $levelA && $x == 1) {
                    $end = substr($place[3], -8);
                    $counterP += $counter;
                    $counter = 0;
                } elseif ($result >= $levelA && $start > 0 && $x == 1) {
                    $str = $start.' '.$end.' '.(100 - $counterP / $mass)."\n";
                    fseek($fd, 0, SEEK_END);
                    fwrite($fd, $str);
                    $counter = 0;
                    $x = 0;
                    $mass = 0;
                }
        }elseif ((double)$place[8] >= 500 || (double)$place[10] > $tim) {
            if ($x == 0) {
                $start = substr($place[3], -8);
                $end = substr($place[3], -8);
                $counterP = 1;
                $str = $start.' '.$end.' '.(100 - $counterP / 1)."\n";
                fseek($fd, 0, SEEK_END);
                fwrite($fd, $str);
                //echo $start,' ', $end, ' ', 100 - $counterP/1,"<br>";
            }else {
                $end = substr($place[3], -8);
                $counterP += 1;
                $str = $start.' '.$end.' '.(100 - $counterP / $mass)."\n";
                fseek($fd, 0, SEEK_END);
                fwrite($fd, $str);
                //echo $start,' ', $end, ' ', 100 - $counterP/$mass,"<br>";
                $mass = 0;
            }
        }elseif ($x == 1) {
            $str = $start.' '.$end.' '.(100 - $counterP / $mass)."\n";
            fseek($fd, 0, SEEK_END);
            fwrite($fd, $str);
            //echo $start,' ', $end, ' ', 100 - $counterP/$mass,"<br>";
            $mass = 0;
        }
    }

}

//function get512mb($fp){

//}
?>