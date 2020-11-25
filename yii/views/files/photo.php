<?php

$user = User::get();
if (!$user->id) {
	$user = User::getMe();;
}
$own = Yii::app()->user->id && $user->id == Yii::app()->user->id;


$contentId = 'content';
include_once 'tabs.php';


$albumId = '';
preg_match('~photo/(\d+)~i', $_SERVER['REQUEST_URI'], $reg);
if ($reg) {
	$albumId = $reg[1];
}

/**
 * Один отдельный альбом
 */
$addUrl = '';
if ($albumId) {

    $album = Lists::model()->findByPk($albumId);
    Lists::addView($albumId);

	$this->setPageHeader($album->name, 1);
	echo 'Фотографии альбома <b>'.$album->name.'</b>. <a href="/files/photo">Вернуться к списку альбомов</a>';

    $images = Media::getAlbumPhotos($user->id, $albumId);

    echo '<p>Всего фотографий в альбоме: <b>' . count($images).'</b></p>';

    if ($_POST['action'] == 'delPhoto') {
        $image = $images[$_POST['key']];
        Media::delPhoto($image);
        Yii::app()->end();
    }

/**
 * Список альбомов
 */
} else {
    $albums = Media::getUserAlbums($user->id);

    if ($_POST['action'] == 'delAlbum') {
        $_POST['key'] = intval($_POST['key']);

        $images = Media::getAlbumPhotos($user->id, $_POST['key']);
        foreach ($images as $image) {
            Media::delPhoto($image);
        }

        Lists::model()->deleteAll(array(
            'condition' => 'id_user='.Yii::app()->user->id.' AND id='.$_POST['key'].''
        ));

        Yii::app()->end();
    }

    if (!count($albums)) {
    	$this->setPageHeader('Фотоальбомы', 1);
    } else {
        $this->setPageHeader('Фотоальбомы ('.count($albums).')', 1);
    }

    if (!$albums) {
    	echo '<p>У вас нет ни одного фотоальбома. Можете их <a href="#" onclick="createAlbum(); '.
            'return false;">добавить</a>.</p>';
    }

    //echo '<p>Всего фотографий в альбоме: <b>' . count($images).'</b></p>';
    $addUrl = '';
    if (!$own) {
    	$addUrl = '?id='.$user->id.'';
    }
}




?>
<div style="padding:10px;">
<?php

if ($images) {
	
    foreach ($images as $k => $image) {
        $image = Utils::getPreview($image, 250, 200);
        
        $a = '';
        if ($own) {
        	$a = '<a href="#" style="color:red" onclick="delPhoto(this, '.intval($k).'); return false;" >
                <img src="/images/del.gif" alt="" /> удалить фото</a>';
        }
    	echo '
        <div style="width:200px; float:left; margin-bottom:10px;">
            <a href="/'.$image.'" rel="gallery" class="fancybox">'.
            '<img src="/'.$image.'" style="max-height:150px" alt="" /></a>
            '.$a.'
        </div>
        ';
    }
    echo '<div class="clear_fix"></div>';

    ?>

    <script type="text/javascript">
    function delPhoto(obj, key)
    {
        $.post('', 'action=delPhoto&key='+key+'', function(data) {
            $(obj).parent().fadeOut();
        });
        return false;
    }
    setTimeout(function() {
        $('.fancybox').fancybox();
    }, 2000);
    </script>

    <div style="margin-top:5px;">
        <a href="#" onclick="return false;">
            <span onclick="this.parentNode.nextSibling.click()">Добавить фотографии в альбом</span>
        </a><input id="uploadFiles" type="file" style="position: absolute; visibility:hidden" name="photo[]"
            onchange="uploadFiles();" multiple />
    </div>

    <?php Utils::uploadCode('uploadFiles', $url='/files/upload/?albumId='.$albumId) ?>

    <?php

} else {

    foreach ($albums as $k => $v) {

        $act = '';
        if ($own) {
        	$act = '<a href="#" style="color:red" onclick="delAlbum(this, '.$v->id.'); return false;" >
                <img src="/images/del.gif" alt="" /> удалить альбом</a>';
        }
        
        $image = Media::getAlbumPreview($user->id, $v->id, $w=350, $h=230);
        $url = '/files/photo/'.$v->id;
        if ($addUrl) {
        	$url .= '/'.$a;
        }
        ?>
        <div style="width:49%; float:left; min-height:250px">
            <div style="padding:10px 0; color:#aaa">Опубликовано <?=Utils::humanDate($v->date_added)?></div>
            <a href="<?=$url?>"><img src="<?=$image?>" alt="" /></a><br />
            <div style="margin:5px 0; color:red"><img src="/images/eye.png" style="width:10px; height:10px; vertical-align: 0px;" alt="" /> <?=intval($v->views)?> <?=$act?></div>
            <h1><a href="<?=$url?>"><?=$v->name?></a></h2>
            <p style="margin:0px; font-size:14px; color:#646464"><?=$v->content?></p>
        </div>
        <?php
    }
    ?>


    <script type="text/javascript">
    function delAlbum(obj, key)
    {
        $.post('', 'action=delAlbum&key='+key+'', function(data) {
            $(obj).parent().parent().fadeOut();
        });
        return false;
    }
    function createAlbum()
    {
        if (name = prompt('Создать новый альбом')) {
            $.get('/files/photoalbumadd/?name='+name, function(data) {
                if (data != '') {
                    if (data == 1) {
                    	location = location
                    } else {
                        alert(data)
                    }
                }
            });
        }
    }
    </script>
    <?php
}

?>
    <div class="clear_fix"></div>
</div>