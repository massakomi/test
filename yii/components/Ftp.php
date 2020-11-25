<?php

class Ftp {

    /**
     * Подключение к Ftp и возвращение идентификатора подключения
     */
    function connect($ftp_server, $ftp_user_name, $ftp_user_pass)
    {
        //Utils::addLog('Коннект к фтп  '.$ftp_server.'... ');
        $conn_id = ftp_connect($ftp_server);
        if (!$conn_id) {
            echo 'Ошибка соединения с '.$ftp_server .' ' . implode(',', error_get_last());
        	Yii::app()->end();
        }
        $login_result = @ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
        if ((!$conn_id) || (!$login_result)) {
            echo "FTP connection has failed! Attempted to connect to $ftp_server for user $ftp_user_name";
            Yii::app()->end();
        }
        return $conn_id;
    }

    /**
     * Скачать файл из From и сохранить в To
     */
    static function download($conn_id, $from, $to)
    {
        if (!$to) {
        	$to = $from;
        }
        $a = @fopen($to, 'w+');
        if (!$a) {
            Utils::addLog('не удалось создать файл '.$to.' для скачивания', 'color:red');
            return false;
        }
        $res = @ftp_fget($conn_id, $a, $from, FTP_BINARY);
        if (!$res) {
            Utils::addLog('не удалось скопировать файл '.$from  . implode(', ', error_get_last()) , 'color:red');
            return false;
        }
        fclose($a);
        return true;
    }

    /**
     * Закачать файл на Ftp - взять с локалки по $source_file и закачать в $destination_file
     */
    function upload($conn_id, $destination_file, $source_file)
    {
        $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);
        if (!$upload) {
            Utils::addLog("$destination_file -- ошибка аплоада файла "  . implode(', ', error_get_last()) );
            return false;
        }
        return true;
    }

    /**
     * Список файлов папки $remoteDir на Ftp
     */
    function ftpList($conn_id, $remoteDir, $level=0)
    {
        if (!$remoteDir) {
            return array();
        }
        if (substr($remoteDir, -1) == '/') {
        	$remoteDir = substr($remoteDir, 0, -1);
        }
        //Utils::addLog('Загружаем ремоте список файлов "'.$remoteDir.'"');
        $files = ftp_rawlist ($conn_id, $remoteDir);
        if (!$files) {
            Utils::addLog('<span style="color:#aaa">нет файлов в "'.$remoteDir.'"</span>');
            return array();
        }
        // Проверка все ли в порядке
        if ($level == 0) {
        	$str = implode('', $files);
        	$err = 0;
            /*if (count($files) < 5) {
            	$err = 1;
            	Utils::addLog('В папке очень мало файлов (меньше 4)');
            }*/
            if (strpos($str, 'DO_NOT_UPLOAD_HERE')) {
            	$err = 1;
            	Utils::addLog('В топ папке есть файл DO_NOT_UPLOAD_HERE');
            }
            if ($err) {
            	exit;
            }
        }
        if (!$files) {
            Utils::addLog('Ошибка получения файлов ' .$remoteDir.' ' . implode(',', error_get_last()));
            return array();
        }
        // Получаем список ремоте файлов указанной папки.
        $filesRemote = array();
        foreach ($files as $k => $v) {
            if (preg_match('~ \.+$~', $v)) {
                continue;
            }
            //  -rw-r--r-- 1 www-data www-data 118104 Jul 25 07:37 53.jpg
            if (!preg_match('~([-rwx]+)\s+(\d+)\s+([\w-]+)\s+([\w-]+)\s+(\d+)'.
                '\s+(\w{3}\s+\d+\s+(?:[\d:]+)?)\s+(.+)~i', trim($v), $reg)) {
                Utils::addLog('<span style="color:red">Не удалось разобрать ftp-строку '.htmlspecialchars($v).'</span>');
                continue;
            }
            $time = 3600*4 + strtotime($reg[6]);
            if ($time > time()) {
            	$time -= 86400 * 365;
            }
            $reg[6] = date('Y-m-d H:i:s', $time);
            $isFolder = $reg[5] == 4096;
            $filesRemote []= array(
                $remoteDir .'/'.$reg[7],
                $reg[7],
                $reg[6],
                $reg[5],
                $isFolder
            );
        }
        $sort_order = array();
        foreach ($filesRemote as $key => $value) {
        	$sort_order[$key] = $value[4];
        }
        array_multisort($sort_order, SORT_DESC, SORT_NUMERIC, $filesRemote);
        return $filesRemote;
    }
}
?>