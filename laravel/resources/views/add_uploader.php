

<p><a id="pickfiles" href="#" class="btn btn-primary btn-sm">Загрузить фотографии</a></p>
<input type="button" class="btn btn-danger btn-sm" style="display:none;" class="initUploaderCancel" value="Отменить загрузку" />
<div id="container">
</div>
<div id="filelist">&nbsp;</div>

<script type="text/javascript">
function callAfterUploadComplete()
{
    setTimeout(function() {
        $('#filelist').hide();
        $('#container').html('');
        $('#filelist').html('');
        $.get('/uploader', function(data) {
            $('#photoUploader').html(data);
            reloadImages()
        });
    }, 2000);
}
</script>




<script type="text/javascript" src="/js/ajaxfileupload/plupload.js"></script>
<script type="text/javascript" src="/js/ajaxfileupload/plupload.html5.js"></script>
<script type="text/javascript" src="/js/ajaxfileupload/plupload.flash.js"></script>

<script type="text/javascript">
/*closeBox = 0;

$('.initUploaderCancel').click(function () {
    closeUploader();
});

function closeUploader()
{
    uploader.stop();
    if (closeBox) {
    	$.fancybox.close();
    }
}*/

var uploader = new plupload.Uploader({
	browse_button : 'pickfiles',
	container: 'container',
	max_file_size : '20mb',
	url : '/filesupload',
	resize : {width : 1024, height : 768, quality : 90},
    flash_swf_url : '/js/ajaxfileupload/plupload.flash.swf',
	silverlight_xap_url : '/js/ajaxfileupload/plupload.silverlight.xap',
	filters : [
		{title : "Image files", extensions : "jpg,jpeg,gif,png,bmp"}
	]
});

// При добавлении файлов, в начале
uploader.bind('FilesAdded', function(up, files) {
    document.getElementById('filelist').innerHTML = '';
    $('#filelist, #container, #uploader').show();
    $('.initUploaderCancel').show();
	for (var i in files) {
		document.getElementById('filelist').innerHTML += '<div id="' + files[i].id + '">' +
            files[i].name + ' (' + plupload.formatSize(files[i].size) + ') <b></b></div>';
	}
    setTimeout(function() {
        if (document.getElementById('agmeTitle') != null) {
            document.getElementById('agmeTitle').disabled = true;
            document.getElementById('agmeTitle').value = 'Идет загрузка. Ждите.';
            setTimeout(function() {
                document.getElementById('agmeTitle').disabled = false;
                document.getElementById('agmeTitle').value = 'Сохранить';
            }, 10000);
        }
        uploader.start();
    }, 500);
});

// В процессе загрузки
countLoaded = 0;
uploader.bind('UploadProgress', function(up, file) {
    countLoaded ++;
    if (document.getElementById(file.id) == null) {
        return ;
    }
	document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
});

// В конце загрузки
uploader.bind('UploadComplete', function(up, file) {
    finishUpload(up)
});

// При ошибке
uploader.bind('Error', function(up, params) {
	alert('Ошибка загрузки файлов '+params.file.name+' '+params.message+' ['+params.code+']');
    finishUpload(up)
});

// В конце загрузки - общее завершение
function finishUpload(up)
{
    if (document.getElementById('agmeTitle') != null) {
        document.getElementById('agmeTitle').disabled = false;
        document.getElementById('agmeTitle').value = 'Добавить объявление';
    }
    if (!$('#uploader').attr('title')) {
    	$('#uploader').attr('title', 0)
    }
    var downloads = parseInt($('#uploader').attr('title'));
    downloads += up.files.length;
    $('#container').html('<span style="font-size:18px; color:green">Фотографии загружены!</span>');
    $('#uploader').html('Загружено фото: '+downloads);
    $('#uploader').attr('title', downloads)
    /*if (closeBox) {
        setTimeout(function() {
            $.fancybox.close();
        }, 1000);
    }*/
    if (typeof(callAfterUploadComplete) != 'undefined') {
    	callAfterUploadComplete();
    }
}

uploader.init();
</script>