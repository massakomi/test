<?php

class FilesController extends Controller
{

    /**
     * Фильтры доступа
     */
    public function filters()
    {
        return array(
            'accessControl'
        );
    }

    /**
     * Правила доступа
     */
    public function accessRules()
    {
        if (in_array($this->action->id, explode(' ', 'audios documents index videoadd video videodelete videoedit upload photo photoalbumadd'))) {
            $this->checkUser();
        }
        return array();
    }
    
    /**
     * Раздел аудиозаписей юзера - добавление, прослушивание
     */
    public function actionAudios()
    {
        $this->setPageHeader('Музыка', true);
        $this->printPage();
    }

    /**
     * Раздел игр - просмотр флеш-игр, доступных юзеру
     */
    public function actionGames()
    {
        $this->setPageHeader('Игры', true);
        $this->printPage();
    }

    /**
     * Раздел радиостанций, доступных для прослушивания
     */
    public function actionRadio()
    {
        $this->setPageHeader('Радиостанции');
        $this->printPage();
    }

    /**
     * Раздел документов - юзер может добавлять и хранить свои файлы и документы, доступные всем или только ему
     */
    public function actionDocuments()
    {
        $this->setPageHeader('Документы');
        $this->printPage();
    }

    /**
     * Раздел видеозаписей юзера
     */
    public function actionIndex()
    {
        if ($_GET['query']) {

            $url = 'https://www.youtube.com/results?search_query=' . urlencode($_GET['query']);
            $content = Utils::loadUrl($url, $cash=0, $expired=86400);
            if (empty($content)) {
                echo 'Пустой контент <a href="'.$url.'">по ссылке</a>';
                return ;
            }

            $data = preg_split('~<div class="yt-lockup-content">~', $content);

            $videos = array();
            $founded = 0;
            foreach ($data as $key => $row) {
                preg_match('~href="(/watch.*?)">([^<]+)</a>~i', $row, $a);
                $href = 'http://youtube.com'.$a[1];
                list($image, $previewMini, $embed) = Video::getPreview($href, $img=2);
                 //'http://youtube.com'.str_replace('/watch', '/embed/', $a[1]);
                $title = trim($a[2]);
                if (!$title) {
                    continue;
                }
                $founded ++ ;
                preg_match('~<a href="(/channel/[a-z\d_]+)" .*?'.'>(.*?)</a></li><li>(.*?)</li><li>(.*?)</li>~i', $row, $a);
                $channelUrl = $a[1];
                $channel = $a[2];
                $channel1 = $a[3];
                $channel2 = $a[4];
                preg_match('~<div class="yt-lockup-description [a-z\d\s-]+" dir="ltr">(.*?)</div>~is', $row, $a);
                $desc = trim($a[1]);
                //preg_match('~src="(.*?)"~i', $row, $a);
                //$image = str_replace('//', 'http://', $a[1]);
                //$videos []= compact('href', 'embed', 'title', 'desc', 'image');

                ?>
                <div style="width:49%; float:left; min-height:300px">
                    <div style="padding:10px 0; color:#aaa">Опубликовано <?=$channel1?> <?=$channel2?>
                        <a href="<?=$channelUrl?>"><?=$channel?></a>
                        <br /><a href="#" style="color:green">добавить себе</a>
                    </div>
                    <a class="fancybox" href="<?=$embed?>"><img src="<?=$image?>" alt=""
                        style="width:350px; height:230px;" /></a><br />
                   <!--
                    <div style="margin:5px 0; color:red"><img src="http://realty/images/eye.png"
                        style="width:10px; height:10px; vertical-align: 0px;" alt="" /> 55</div>
                   -->
                    <h1><a class="fancybox" href="<?=$embed?>"><?=mb_substr($title, 0, 50)?></a></h2>
                    <p style="margin:0px; font-size:14px; color:#646464"><?=$desc?></p>
                </div>
                <?php
                if ($key > 0 && $key % 2 == 0 ) {
                	echo '<div class="clear_fix"></div>';
                }
            }
            if (!$founded) {
            	box('Ничего не найдено');
            }

            echo '<div class="clear_fix"></div>
            <script type="text/javascript"> frameEvents() </script> ';

            //echo '<pre>'; print_r($videos); echo '</pre>';

            Yii::app()->end();
        }
        $this->setPageHeader('Видеозаписи', 1);
        $this->printPage();
    }

    /**
     * Форма добавления видеозаписи на сайт
     */
    public function actionVideoAdd()
    {
        $afterSave = 'location.href = \'/files/\';';
        if ($_GET['callAfter']) {
        	$afterSave = $_GET['callAfter'] .'(data);';
        }
        ?>
        <div class="fancy-title">Добавить видео</div>
        <div class="font_default video">
            <div>
            <input type="text" value="" onkeyup="document.getElementById('addVideoButton').disabled = false" class="form-control" placeholder="Ссылка на видеоролик" id="uploadVideoField" style="width:95%; padding:5px;" />
            <div style="margin-top:5px;"><input disabled id="addVideoButton" class="btn btn-info" type="button" onclick="addVideoFile(); return false;" value="Добавить видео"></div>
            </div>

            <input id="box_controls_button" style="display:none; margin-top:10px;"
                type="button" class="btn btn-success" value="Сохранить">
            <div id="results"> </div>
            <br /><br />
        </div>
        <script type="text/javascript">
        function addVideoFile()
        {
            var val = document.getElementById('uploadVideoField').value;
            if (val.length < 10) {
                return;
            }
            if (val.indexOf('http') < 0 || val.indexOf('www') < 0 ) {
                alert('Неправльная ссылка на видеофайл');
                return ;
            }
            $.post('/files/video', 'url=' + val, function(data) {
                $('#results').html(data);
                if (data.length > 50) {
                    $('#box_controls_button').show();
                }
                $.fancybox.update();
                $.fancybox.reposition()
            });
        }
        callWithTimeout('#uploadVideoField', addVideoFile)

        $('#box_controls_button').click(function () {
            document.getElementById('box_controls_button').disabled = true;
            videoform = $('#videoform').serialize();
            $('#videoform input, #videoform textarea').val('');
            $.post('/files/video', videoform, function(data) {
                document.getElementById('box_controls_button').disabled = false;
                if (data) {
                    alert(data);
                } else {
                    <?=$afterSave?>
                }
                 
            });
        });
        </script>
        <?php
        Yii::app()->end();
    }

    /**
     * Действие добавления видео с предварительным извлечением инфо видео по ссылке
     */
    public function actionVideo()
    {
        if (array_key_exists('embed', $_POST)) {
            $a       = new Media;
            $a->path = $_POST['embed'];
            $a->url  = $_POST['url'];

            $a->title        = $_POST['title'];
            $a->description  = $_POST['content'];
            $a->date_added   = date('Y-m-d H:i:s');
            $a->id_user      = Yii::app()->user->id;
            $a->type         = 'video';
            $a->access       = intval($_POST['access']);

            $a->preview      = $_POST['preview'];
            $a->preview_mini = $_POST['preview_mini'];
            if (!$a->save()) {
                echo CHtml::errorSummary($a);
            }
            Yii::app()->end();
        }

        $url = $_POST['url'];
        if (empty($url)) {
            echo 'Пустой урл';
            return '';
        }
        list($previewUrl, $previewMiniUrl, $embedUrl) = Video::getPreview($url, $img = 2);

        if (!$embedUrl) {
            echo 'Неправильная ссылка на видео!';
            return ;
        }
        list($title, $description) = Video::extractInfo($url);
        ?>
        <form method="post" id="videoform" style="margin-top:10px;">
            <div class="form-group">
                <label>Изображение</label>
                <img style="width:300px; display:block;" src="<?=$previewUrl?>" />
            </div>
            <div class="form-group">
                <label>Название</label>
                <input type="text" name="title" value="<?=$title?>" class="form-control" />
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea name="content" class="form-control"><?=$description?></textarea>
            </div>
            <div class="checkbox">
                <label>
                <input type="checkbox" checked name="access" value="1" /> доступно всем
                </label>
            </div>
<!--
            <b>Изображение</b>
            <p><img style="width:300px;" src="<?=$previewUrl?>" /></p>
            <b>Название</b>
            <p><input type="text" name="title" value="<?=$title?>" class="text" /></p>
            <b>Описание</b>
            <p><textarea name="content" style="width:95%;height:200x;"><?=$description?></textarea></p>
            <p><label><input type="checkbox" checked name="access" value="1"> доступно всем</label></p>
-->
            <input type="hidden" name="embed" value="<?=$embedUrl?>">
            <input type="hidden" name="url" value="<?=$url?>">
            <input type="hidden" name="preview" value="<?=$previewUrl?>">
            <input type="hidden" name="preview_mini" value="<?=$previewMiniUrl?>">
        </form>
        <?php
        Yii::app()->end();
    }

    /**
     * Удаление видеозаписи со своей страницы
     */
    public function actionVideodelete()
    {
        $id = $_POST['id'];
        $res = Media::model()->deleteAllByAttributes(array(
            'id_user' => Yii::app()->user->id,
            'id' => intval($id)
        ));
        if (!$res) {
            echo 'Ошибка удаления';
        }
        Yii::app()->end();
    }

    /**
     * Редактирование видезаписи - форма и действие
     */
    public function actionVideoedit()
    {
        $id = $_REQUEST['id'];
        $video = Media::model()->findByAttributes(array(
            'id' => intval($id),
            'id_user' => Yii::app()->user->id
        ));
        if (!$video) {
            echo 'Видео '.$id.' не найдено';
            return '';
        }
        if ($_POST) {
            $video->title = strip_tags($_POST['title']);
            $video->description = strip_tags($_POST['content']);
            $video->access = intval($_POST['access']);
            $res = $video->save();
            if (!$res) {
                echo strip_tags(CHtml::errorSummary($video));
            }
            Yii::app()->end();
        }
        ?>

        <div class="fancy-title">Редактировать видео</div>
        <form method="post" class="wideform" id="videoform">
            <input type="hidden" name="id" value="<?=$id?>">
            <div class="form-group">
            <label>Название</label>
            <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($video->title)?>" />
            </div>
            <div class="form-group">
            <label>Описание</label>
            <textarea name="content" class="form-control"><?=htmlspecialchars($video->description)?></textarea>
            </div>

            <div class="checkbox">
            <label><input type="checkbox" <?=$video->access?'checked':''?> name="access" value="<?=$video->access?>"> доступно всем</label>
            </div>

            <input type="submit" class="btn btn-info" name="submit" value="Сохранить">
        </form>
        <?php
        Yii::app()->end();
    }


    /**
     * Раздел фотографий юзера
     */
    public function actionPhoto()
    {
        $this->printPage('');
    }

    /**
     * Добавление фотоальбома - действие
     */
    public function actionPhotoalbumadd()
    {
        $album = new Lists;
        $album->type = 'photo-album';
        $album->name = htmlspecialchars(strip_tags($_GET['name']));
        $album->date_added = date('Y-m-d H:i:s');
        $album->id_user = Yii::app()->user->id;
        $res = $album->validate();
        if (!$res) {
            echo CHtml::errorSummary($album);
            Yii::app()->end();
        }
        $album->save();
        echo 1;
        Yii::app()->end();
    }

    /**
     * Действие загрузки фотографии на сайт с полным кодом и всеми проверками
     */
    public function actionUpload()
    {
        if (empty($_FILES['photo'])) {
            echo 'alert("Пусто");';
            return ;
        }
        $id = Yii::app()->user->id;

        $maxsize       = Yii::app()->params['add_image_maxsize'];
        $minwidth      = Yii::app()->params['add_image_minwidth'];
        $maxwidth      = Yii::app()->params['add_image_maxwidth'];
        $dir           = 'upload/photos';
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        $dir           = 'upload/photos/'.$id;
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        $albumId       = intval($_GET['albumId']);
        if (empty($albumId)) {
            echo 'alert("Пустой ид альбома");';
            return ;
        }
        $dir           = 'upload/photos/'.$id.'/'.$albumId;
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        $allowed_types = Yii::app()->params['avatar_allowed_types'];
        $allowed_ext   = Yii::app()->params['avatar_allowed_ext'];

        $errors = array();
        $uploaded = 0;
        foreach ($_FILES['photo']['name'] as $k => $name) {
            $name     = $_FILES['photo']['name'][$k];
            $size     = $_FILES['photo']['size'][$k];
            $tmp_name = $_FILES['photo']['tmp_name'][$k];
            $type     = $_FILES['photo']['type'][$k];

            if ($size > $maxsize) {
                $errors [$name][]= 'Размер изображения должен быть не более '.Utils::formatSize($maxsize);
                continue;
            }
            list($width, $height) = getimagesize($tmp_name);
            if (!$width) {
                $errors [$name][]= 'Не удалось прочитать размер изображения';
                continue;
            }
            if ($width > $maxwidth) {
                $errors [$name][]= 'Ширина изображения должна быть не более '.$maxwidth.'px';
                continue;
            }
            if ($width < $minwidth) {
                $errors [$name][]= 'Ширина изображения должна быть не менее '.$minwidth.'px';
                continue;
            }
            if (!in_array($type, $allowed_types)) {
                $errors [$name][]= 'Неразрешенный тип файла ('.$type.')';
                continue;
            }

            $name_explode = explode('.', $name);
            $extension = mb_strtolower($name_explode[count($name_explode) - 1]);
            if (!in_array($extension, $allowed_ext)) {
                $errors [$name][]= 'Неразрешенное расширения файла ('.$extension.')';
                continue;
            }

            $a = new Media;
            $a->path = $dir;
            $a->type = 'photo';
            $a->date_added = date('Y-m-d H:i:s');
            $a->id_user = Yii::app()->user->id;
            if (!$a->save()) {
                $errors [$name][]= strip_tags(CHtml::errorSummary($a));
                continue;
            }
            $a->path =  $dir . '/' . $a->id . '.' . $extension;
            //$a->preview =  $dir . '/' . $a->id . '_preview.' . $extension;
            $a->save();

            $res = move_uploaded_file($tmp_name, $a->path);
            if (!$res) {
                $errors [$name][]= 'Ошибка сохранения файла';
                continue;
            }
            /*$img = new ImageGD;
            $img->load($a->path);
            $img->crop(120, 80, $cropType = null);
            $img->save($a->preview, false);*/
            $uploaded++;
        }

        $content = '';
        if ($uploaded > 0) {
            $content = 'Успешно загружено файлов: '.$uploaded.' ';
        }

        if ($errors) {
            $content = array($content);
            foreach ($errors as $filename => $errs) {
                $content []= 'Ошибки при загрузке файла "'.$filename.'": '. implode(', ', $errs);
            }
            $content = ''.implode(' ', $content).'';
        }

        $content = '\''.str_replace("'", "\'", (preg_replace("~[\s\n]+~us", ' ', $content))).'\'';
        echo 'alert('.$content.');';
        if (!$errors) {
            echo 'location.href=location.href;';
        }


        Yii::app()->end();
    }



    public function actionTest()
    {
        $file = $_GET['file'];
        echo '<img src="/'.$file.'" style="max-height:300px;" alt="" />';
        (new Files)->positionsInfo($file);
    }

    public function actionTest2()
    {

        $addMore = ' AND id_nedv=1 AND id_type IN (41,42,43,44,64,65,66,31,32,67) ';

        /*$sql = 'select id_item from zz_object_params where id_attribute=601';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        $ids = [];
        foreach ($data as $k => $v) {
            $ids []= $v['id_item'];
        }
        $addMore .= ' AND (id IN ('.implode(',', $ids).') OR flat>0)';*/
        $where = 'id_user IN ('.implode(',', User::dpUsers()).')'.$addMore.' AND '.ObjectCondition::getStatusCondition();

        $alls = Objects::model()->findAll([
            'condition' => $where,
            'limit' => 50
        ]);

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
            <title>Test</title>
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script type="text/javascript">
        function cutter(obj, position, filename)
        {
            $.post('/files/watermarkdel', 'filename='+filename+'&position='+position, function(data) {
                $(obj).parent().parent().slideUp()
            });
        }
        </script>
        </head><body>
        
        <?php

        $files = new Files;
        foreach ($alls as $item) {

            $images = $item->getImages();
            foreach ($images as $k => $v) {
                $fullimage = substr($v[0], 1);

                $info = '';
                $positions = $files->positionsInfo($fullimage, $info);
                if (count($positions) == 1) {
                    continue;
                }
                

                echo '<hr /><div><a href="/files/test?file='.$fullimage.'">'.$fullimage.'</a><br />';
                echo '<img src="/'.$fullimage.'" style="max-height:300px;" alt="" />';
                //if (count($positions)) {
                	echo '<div>Позиция - <b>'.implode(',', $positions).'</b></div>';
                    echo $info;
                    echo '<div><a href="#" onclick="cutter(this, \'top\', \''.$fullimage.'\'); return false;">top</a> <a href="#" onclick="cutter(this, \'top\', \''.$fullimage.'\'); return false;">right</a></div></div>';
                //}


                //$src = ImageGD::imageCut($fullimage, [$position => 75]);
                //imagejpeg($src, $fullimage, 80);

            }
        }
        ?>
        <?php
    }

    public function actionWatermarkdel()
    {

        if ($_POST['filename']) {
            $src = ImageGD::imageCut($_POST['filename'], [$_POST['position'] => 75]);
            imagejpeg($src, $_POST['filename'], 80);
            exit;
        }

        $id = intval($_GET['id']);
        $item = Objects::model()->findByPk($id);
        if (!$item->id) {
            echo 'Объявление не найдено';
            return ;
        }
        /*if (!$item->haswater) {
            echo 'Водяных знаков на фотографиях нет';
            return ;
        }*/

        $position = $_GET['position'];

        $item->deleteWatermarks($position);

        $item->clearPreviews();
        
        echo 'Водяные знаки удалены с '.$position.'!';
    }
}

