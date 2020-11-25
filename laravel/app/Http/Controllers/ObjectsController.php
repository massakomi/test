<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Objects;
use Session;
use App\Offers;
use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ObjectsController extends Controller
{

    public function addForm(Request $request)
    {

        self::clearUserFolder();

        return view('add', [
            //'username' => Auth::user()->name,
            'item' => new Objects,
            'title' => 'Подать объявление',
            'btnText' => 'Добавить объявление',
            'addedTxt' => 'Объявление добавлено!'
        ]);
    }

    public function editForm($id)
    {

        if (!Auth::check()) {
        	return redirect('/auth/login');
        }

        return view('add', [
            //'username' => Auth::user()->name,
            'item' => Objects::find($id),
            'title' => 'Редактировать объявление',
            'btnText' => 'Сохранить объявление',
            'addedTxt' => 'Объявление изменено!'
        ]);
    }

    public function add(Request $request)
    {

        $editMode = false;
        if ($_POST['id']) {
        	$object = Objects::find($_POST['id']);
            $editMode = true;
        } else {
	        $object = new Objects;
        }

        // русские названия полей - resources\lang\en/validation.php
        $messages = [
          'required' => ':attribute обязательно',
          'offer.required' => 'Необходимо ввести хотя бы одно предложение',
          'min'      => 'Не меньше :min символов',
          'same'    => ':attribute и :other должны совпадать.',
          'size'    => ':attribute должен быть равен :size.',
          'integer'    => ':attribute должна быть числом',
          'between' => ':attribute должен быть между :min и :max.',
          'in'      => ':attribute должен иметь один из следующих типов: :values',
        ];

        $all = $request->all();

        $offersEdit = [];
        if (isset($all['offer-edit'])) {
            $offersEdit = $all['offer-edit'];
            unset($all['offer-edit']);
        }


        // Инвертируем массив офферов
        $i = 0;
        $offers = [];
        while (true) {
            if (!isset($all['offer']['price'][$i])) {
            	break;
            }
            foreach ($all['offer'] as $key => $val) {
                $offers [$i][$key]= $val[$i];
            }
            $i ++;
        }
        foreach ($offers as $k => $v) {
            if (implode('', $v) == '') {
            	unset($offers[$k]);
            }
        }
        if (!$offers && !$offersEdit) {
        	unset($all['offer']);
            echo json_encode(['offers-errors' => 'Необходимо ввести хотя бы одно предложение']);
            exit;
        }
        //echo '<pre>'; print_r($offers); echo '</pre>';

        // - файл
        if (empty($all['files'][0])) {
        	unset($all['files']);
        }

        // Проверка основных данных
        $rules = [
            'id_nedv' => 'required|integer',
            'type' => 'required|min:3',
            'address' => 'min:10',
            'content' => 'min:5',
            'price_about' => 'min:5',
            'so' => 'numeric',
            'offer' => 'array|required',
            'phone' => 'required',
        ];
        if (!$object->getImages()) {
            /*$rules = array_merge($rules, [
                'files' => 'required',
                'files.*' => 'image',
            ]);*/

            $targetDir = self::getCurrentFolder();
            $a = scandir($targetDir);
            $images = [];
            foreach ($a as $k => $v) {
                if ($v == '.' || $v == '..') {
                    continue;
                }
                $path = $targetDir.'/'.$v;
                $images []= $path;
            }
            if (!$images) {
            	exit(json_encode(['photo-errors' => 'Вы не загрузили ни одной фотографии!']));
            }
        }
        $validator = Validator::make($all, $rules, $messages);
        if ($validator->fails()) {
            echo json_encode($messages = $validator->messages());
            exit;
        }


        // Проверка и сборка офферов в один массив
        $offersErrors = [];
        $offersSave = [];
        $index = 0;
        foreach ($offers as $vals) {
            $index ++;
            $price = $vals['price'];
            $rent = $vals['price_rent'];
            $so = $vals['so'];
            $file = $vals['file'];
            $price = preg_replace('~[^\d]~i', '', $price);
            $rent = preg_replace('~[^\d]~i', '', $rent);
            $offerData = ['price' => $price, 'price_rent' => $rent, 'so' => $so, 'file' => $file];

            $validator = Validator::make($offerData, [
                'price' => $all['id_nedv'] == 1 ? 'required|numeric|min:4' : '',
                'price_rent' => 'required|numeric|min:4',
                'so' => 'required|numeric',
                'file' => 'required|image'
            ], $messages);

            if ($validator->fails()) {
                $offersErrors [$index]= $validator->messages();
            } else {
                $offersSave []= $offerData;
            }

        }

        if ($offersErrors) {
            echo json_encode(['offers-errors' => $offersErrors]);
            exit;
        }

        // Все хорошо, создаем объект
        //$all ['user_id'] = Auth::id();
        if ($object->id) {
        	$object->fill($all);
            $object->save();
        } else {
            $object = Objects::create($all);
        }

        // Сохраняем фотки объекта, если есть
        /*$files = $request->file('files');
        if($request->hasFile('files'))
        {
            foreach($files as $image)
            {
                $name = $image->getClientOriginalName();
                $dir = public_path().'/images/items/'.$object->id;
                if (!file_exists($dir)) {
                	mkdir($dir);
                }
                $image->move($dir, $name);
            }
        }*/
        if ($images) {
            foreach ($images as $image) {
                //$name = $image->getClientOriginalName();
                $dir = public_path().'/images/items/'.$object->id;
                if (!file_exists($dir)) {
                	mkdir($dir);
                }
                rename($image, $dir.'/'.basename($image));
            }
        }

        // Сохраняем предложения объекта и их фотки
        foreach ($offersSave as $offerData) {
            $offer = new Offers($offerData);
            $object->offers()->save($offer);
            if ($offer->id) {
                $dir = public_path().'/images/offers/'.$object->id;
                $offerData['file']->move($dir, $offer->id.'.jpg');
            }
        }

        echo json_encode(['card-id' => $object->id]);
    }

    function filesUploadCheck($fileName, $size)
    {
        $allowedExt = explode(' ', 'png jpg jpeg gif png bmp');
        $ext = Utils::extension($fileName);
        if (!in_array($ext, $allowedExt)) {
            $msg = 'Недоупстимое расширение файла ('.$ext.')';
            Utils::log($msg, 'error');
            exit;
        }

        if ($size > 1024 * 1024 * 15) {
            $msg = 'Слишком большой файл ('.$fileName.')';
            Utils::log($msg, 'error');
            exit;
        }
    }

    public function itemimages()
    {
        if (isset($_GET['id'])) {
            $images = Objects::getImages($error, $count, (int)$_GET['id']);
            if (!count($images)) {
                return ;
            }
            foreach ($images as $k => $v) {
                $isMain = basename($item->photo) == basename($v[0]);
                $del = ' onclick="deletePhoto(this, \''.basename($v[0]).'\'); return false;" title="Удалить фотографию"';
                $rtLeft = ' onclick="rotatePhoto(this, \''.basename($v[0]).'\', 1); return false;" title="Повернуть влево"';
                $rtRight = ' onclick="rotatePhoto(this, \''.basename($v[0]).'\', 0); return false;"
                    title="Повернуть вправо"';

            	echo '
                <div class="im">
                    <img src="'.$v[1].'?'.rand(1,10000).'" onclick="mainPhoto(this, \''.basename($v[0]).'\'); return false;"
                        class="image'.($isMain?' main':'').'" alt="" />
                    <div style="width:16px; float:right; padding-right:">
                        <a href="#"'.$del.'><img src="/images/del.gif" alt="" /></a>
                        <a href="#"'.$rtLeft.'><img src="/images/rotate_left.png" style="width:12px;" alt="" /></a>
                        <a href="#"'.$rtRight.'><img src="/images/rotate_right.png" style="width:12px;" alt="" /></a>
                    </div>
                </div>';
            }
        }


        $targetDir = self::getCurrentFolder();
        $a = scandir($targetDir);
        foreach ($a as $k => $v) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            $path = $targetDir.'/'.$v;
            $del = ' onclick="deletePhoto(this, \''.$v.'\'); return false;" title="Удалить фотографию"';
        	echo '
            <div class="im">
                <img src="/'.$path.'?'.rand(1,10000).'" style="border: 5px solid blue;" class="image" alt="" />
                <div style="width:16px; float:right; margin-left:5px">
                    <a href="#"'.$del.'><i class="fas fa-times" style="color:red;"></i></a>
                </div>
            </div>';
        }

    }

    public static function getCurrentFolder()
    {
        $targetDir = 'upload/tmp';
        if (!file_exists($targetDir)) {
        	exit('dir not exists '.$targetDir);
        }
        $folder = session('folder');
        if (!$folder) {
            $folder = rand(1000,9999999);
            Session::set('folder', $folder);
        }
        $targetDir .= '/'.$folder;
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir)) {
                throw new Exception('Ошибка создания папки');
            }
        }
        return $targetDir;
    }

    public static function clearUserFolder()
    {
        $dir = self::getCurrentFolder();
        if ($dir && file_exists($dir)) {
            $a = glob($dir.'/*');
            foreach ($a as $k => $v) {
            	unlink($v);
            }
            rmdir($dir);
        }
    }

    public function filesupload()
    {
        if (empty($_FILES['file'])) {
            echo '< пустой FILES';
            return ;
        }

        // Objects::clearTempFiles();

        $targetDir = self::getCurrentFolder();
        $a = scandir($targetDir);
        $maxIndex = 0;
        foreach ($a as $k => $v) {
            if ($v == '.' || $v == '..') {
                continue;
            }
            $maxIndex = max($maxIndex, intval($v));
        }
        $maxIndex ++;

        $filename = @$_REQUEST["name"];
        $ext = Utils::extension($filename);
        $filename = $maxIndex .'.'.$ext;


        self::processMegaUpload($targetDir, $filename);
    }

    public function uploader()
    {
        return view('add_uploader');
    ?>
        <a id="pickfiles" href="#" class="btn btn-info">Загрузить фотографии</a>
        <input type="button" class="btn btn-danger" style="display:none;" class="initUploaderCancel" value="Отменить загрузку" />
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
                $.get('/object/uploader', function(data) {
                    $('#photoUploader').html(data);
                    reloadImages()
                });
            }, 2000);
        }
        </script>

        <?php
        Utils::initUploader('/object/filesupload', array('resize' => 1, 'closeBox' => false));

    }


    /**
     * Механизм обработки мультиаплоада plupload на стороне сервера в одном методе
     * @param string Папка, куда складывать файлы
     * @param string Функция, которую надо вызвать для проверки размера и имени файла
     * @param string Как назвать файл (иначе автоматическое название)
     *
     * Files::processMegaUpload($targetDir, 'filesUploadCheck');
     */
    function processMegaUpload($targetDir, $fileNameSave='', $addWatermark=1)
    {

        //Utils::log('processMegaUpload '.$targetDir.' AS '.$fileNameSave.'', 'info');

        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        if ($fileNameSave) {
        	$fileName = $fileNameSave;
        } else {
            $fileName = Utils::translit($fileName);
            $fileName = preg_replace('/[^\w\._]+/', '-', $fileName);
            $fileName = str_replace('_', '-', $fileName);
        }

        // Utils::log('Save as '.$fileName.'', 'info');

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
        	$ext = strrpos($fileName, '.');
        	$fileName_a = substr($fileName, 0, $ext);
        	$fileName_b = substr($fileName, $ext);

        	$count = 1;
        	while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
        		$count++;

        	$fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Create target dir
        if (!file_exists($targetDir))
        	@mkdir($targetDir, 0777);

        // Remove old temp files
        if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
        	while (($file = readdir($dir)) !== false) {
        		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

        		// Remove temp file if it is older than the max age and is not the current file
        		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) &&
                    ($tmpfilePath != "{$filePath}.part")) {
        			@unlink($tmpfilePath);
        		}
        	}

        	closedir($dir);
        } else {
            Utils::log($e = 'Failed to open temp directory. '.Yii::app()->user->id.' ', 'error');
        	die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "'.$e.'"}, "id" : "id"}');
        }


        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
        	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
        	$contentType = $_SERVER["CONTENT_TYPE"];

        // У файла есть расширение, тип, размер. Проверяем тп. Файлы загружаются поочереди.

        $allowedExt = explode(' ', 'png jpg jpeg gif png bmp');
        $ext = Utils::extension($fileName);

        Utils::log($fileName .' - '.$_FILES['file']['type'] .' - '.$_FILES['file']['size'], 'info');

        $size = $_FILES['file']['size'];
        self::filesUploadCheck($fileName, $size);

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
        	if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        		// Open temp file
        		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        		if ($out) {
        			// Read binary input stream and append it to temp file
        			$in = fopen($_FILES['file']['tmp_name'], "rb");

        			if ($in) {
        				while ($buff = fread($in, 4096))
        					fwrite($out, $buff);
        			} else {
                        Utils::log($e = 'Failed to open input stream. '.Yii::app()->user->id.' ', 'error');
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.$e.'"}, "id" : "id"}');
                    }
        			fclose($in);
        			fclose($out);
        			@unlink($_FILES['file']['tmp_name']);
        		} else {
                    Utils::log($e = 'Failed to open output stream. '.Yii::app()->user->id.' ', 'error');
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "'.$e.'"}, "id" : "id"}');
                }

        	} else {
                Utils::log($e = 'Failed to move uploaded file. '.Yii::app()->user->id.' ', 'error');
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "'.$e.'"}, "id" : "id"}');
            }

        } else {
        	// Open temp file
        	$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        	if ($out) {
        		// Read binary input stream and append it to temp file
        		$in = fopen("php://input", "rb");

        		if ($in) {
        			while ($buff = fread($in, 4096))
        				fwrite($out, $buff);
        		} else {
                    Utils::log($e = 'Failed to open input stream. '.Yii::app()->user->id.' ', 'error');
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.$e.'"}, "id" : "id"}');
                }

        		fclose($in);
        		fclose($out);
        	} else {
                Utils::log($e = 'Failed to open output stream. '.Yii::app()->user->id.' ', 'error');
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "'.$e.'"}, "id" : "id"}');
            }
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
        	rename("{$filePath}.part", $filePath);
            chmod($filePath, 0777);
        }

        if (in_array($ext, explode(' ', 'png jpg jpeg gif png bmp'))) {
        	//ImageGD::fixImageRotate($filePath);
        }

        if ($addWatermark) {
        	//Utils::addWatermark($filePath);
        }

        // Return JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

}
