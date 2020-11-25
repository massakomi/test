<?php

$user = User::get();
if (!$user->id) {
	$user = User::getMe();;
}
$own = Yii::app()->user->id && $user->id == Yii::app()->user->id;

$this->setUserTitle($user, '', ' - файлы');
            
$contentId = 'content';
include_once 'tabs.php';

?>

<div style="padding:10px;" id="files_content_wrap">

<form method="post" style="margin:0 0 10px;">
    <b style="font-size:14px;">Поиск на youtube</b> &nbsp;
    <input type="text" name="query" id="tubeQuery" style="width:450px; padding:5px;" class="text" />
    <input type="submit" value="Искать" id="tubeBtn" class="btn" onclick="searchTube(this); return false;" />
</form>
<div id="videoList">

<?php if ($own) { ?>
<p style="font-size:14px; margin-top:0px; padding-top:0px;"><a href="#" onclick="uploadVideo(); return false;" style="color:green">Добавить новое видео</a></p>
<?php } ?>


<?php
$videos = Media::getUserVideos($user->id);
$countAll = count($videos);

foreach ($videos as $k => $v) {
    $date = Utils::HumanDatePrecise(strtotime($v->date_added));
    $add = '';
    if ($own) {
    	$add  = '<a href="#" onclick="removeVideo('.$v->id.', this.parentNode.parentNode); return false;"><img src="/images/del.gif" alt="" /></a>&nbsp;';
    	$add .= '<a href="#" onclick="editVideo('.$v->id.'); return false;"><img src="/images/edit2.png" style="width:14px;" alt="" /></a>';
    }
    ?>
    <div style="width:49%; float:left; min-height:250px">
        <div style="padding:10px 0; color:#aaa">Опубликовано <?=$date?> <?=$add?></div>
        <a class="iframe" href="<?=$v->path?>"><img src="<?=$v->preview?>" alt="" style="width:350px; height:230px;" /></a><br />
        <div style="margin:5px 0; color:red"><img src="/images/eye.png" style="width:10px; height:10px; vertical-align: 0px;" alt="" /> <?=intval($v->views)?></div>
        <h1><a class="iframe" href="<?=$v->path?>"><?=$v->title?></a></h2>
        <p style="margin:0px; font-size:14px; color:#646464"><?=$v->description?></p>
    </div>
    <?php
}
?>

    <div class="clear_fix"></div>
</div>

</div>



<script type="text/javascript">

function searchTube(btn)
{
    var q = document.getElementById('tubeQuery').value;
    if (!q) {
        return ;
    }
    loader();
    btn.disabled = true;
    $.get('/files/?query=' + q, function(data) {
        loader();
        btn.disabled = false;
        $('#videoList').html(data);
    });
}

$(document).ready(function(){
    setTimeout(function() {
        //document.getElementById('tubeQuery').value = 'сыктывкар';
        //searchTube(document.getElementById('tubeBtn'));

        frameEvents()
    }, 1000);
});


function frameEvents()
{
    $('.iframe').fancybox({
        width: 1100,
        height: 900
    })
}

function getFormContent() {
    var formContent = '';
    return formContent;
}

function uploadVideo() {
    $.get('/files/videoadd', function(data) {
        sbox('Новое видео', data, 500);
    });
}

function editVideo(id) {
    $.get('/files/videoedit/?id=' + id, function(data) {
        var box = new MessageBox({dark: true, title: 'Редактировать видео', hideButtons: true, width:500});
        show(boxLayerBG);
        box.show();
        box.content(data);
        $('#videoform').submit(function () {
            $.post('/files/videoedit', $('#videoform').serialize(), function(data) {
                if (data != '') {
                    alert(data)
                } else {
                    location = location;
                }
            });
            return false;
        });
    });
};

function removeVideo(id, obj) {
    if (!confirm('Удалить это видео?')) {
        return false;
    }
    $.post('/files/videodelete', 'id=' + id, function(data) {
        if (data != '') {
        	alert(data)
        } else {
            $(obj).slideUp()
        }
    });
};

</script>