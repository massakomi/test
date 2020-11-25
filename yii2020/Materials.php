<?php

class Materials extends CActiveRecord
{

    protected $deprecatedAttributes;
    public $limit = 20, $offset = 0, $sortField = 't.id', $sortDir = 'ASC', $id, $userId = 0, $createdBy = 0, $images;

    public $statuses = [
        0 => 'На размещении',
        1 => 'Размещен',
        2 => 'В архиве',
    ];

    public function tableName()
    {
        return 't_materials';
    }

    public function AttributeNames()
    {
        return array();
    }

    public function beforeSave()
    {
        if ($this->scenario == "save" or $this->scenario == "update") {
            $this->createdBy = yii::app()->user->id;
            $this->userId = Yii::app()->user->id;
            $this->prepareDate($this, array('start'), true);
        }
        $this->deprecatedAttributes['published'] = $this->published;
        return parent::beforeSave();
    }

    public function afterDelete($ids=[])
    {
        if (!$ids && $this->id) {
        	$ids = [$this->id];
        }
        if ($ids) {
            foreach ($ids as $id) {
                $this->removeImages($id);
                MaterialsDirections::model()->deleteAllByAttributes(['materialId' => $id]);
            }
        }
        return parent::beforeDelete();
    }

    public function afterSave()
    {
        $model = Materials::model()->find(array(
          'select' => 'MAX(id) id',
        ));
        $this->id = $model->id;
        if ($_POST['materials']['directions']) {
        	MaterialsDirections::model()->saveBatch($this->id, $_POST['materials']['directions']);
        }
        return parent::afterSave();
    }

    public function rules()
    {
        return array(
            array('id, start, name, published, categoryId, userId, user, status, directions, unit, price, volume, object, condition', 'safe'),
            array('name', 'required'),
            array('images', 'file', 'types'=>'jpg,gif,png,jpeg', 'maxSize'=>1024*1024*10, 'allowEmpty'=>true, 'maxFiles'=>10),
        );
    }

    public function relations()
    {
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'userId'),
            'author' => array(self::BELONGS_TO, 'User', 'createdBy'),
            'category' => array(self::BELONGS_TO, 'Categories', 'categoryId'),
            'materialsDirections' => array(self::HAS_MANY, 'MaterialsDirections', array('materialId' => 'id')/*, 'through' => 'directions'*/),
            'directions' => array(self::HAS_MANY, 'Directions', array('groupId' => 'id'), 'through' => 'materialsDirections'),
        );
    }

    public function getLimitedSet()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'id, start, end, name, published, userId, status';

        if (!empty(Pagination::component()->search)) {
            $fields = explode(',', 't.id, t.start, t.name, t.published, t.userId, t.createdBy, category.name');
            foreach ($fields as $field) {
                $criteria->compare(trim($field), Pagination::component()->search, true, 'OR');
            }
        }
        Pagination::component()->totalItems = $this->with(array(
                    'category' => array(
                        'select' => 'name'
            )))->count($criteria);
        $criteria->order = Pagination::component()->sortField . ' ' . Pagination::component()->sortDir;
        $criteria->limit = Pagination::component()->limit;
        $criteria->offset = Pagination::component()->offset;
        return $this->with(array(
            'category' => array(
                'select' => 'name'
            ),
            'user' => array(
                'select' => 'login, id'
            ),
            'author' => array(
                'select' => 'fio, full_company_name, id'
            ),
            'directions' => array(
                'select' => 'name, id'
        )))->findAll($criteria);
    }

    public function getSingle()
    {
        if (!empty($this->id)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'id, start, end, name, published, userId, status, unit, volume, object, price, condition';
            $criteria->condition = 't.id = :id';
            $criteria->params = array(':id' => $this->id);
            $tender = $this->with(array(
                'user' => array(
                    'select' => 'login, id'
                ),
                'directions' => array(
                        'select' => 'name, id'
            )))->find($criteria);
            $this->prepareDate($tender, array('start'));

            $files = $this->getImages();

            $directions = [];
            if ($tender->directions) {
                foreach ($tender->directions as $k => $v) {
                    $directions []= $v->id;
                }
            }

            return array_merge(array_filter($tender->attributes, function($var) {
                        return !is_null($var);
                    }), array('user' => !empty($tender->user) ? $tender->user->attributes : array(), 'directions' => $directions, 'files' => $files));
        } else {
            return array();
        }
    }

    private function prepareDate(&$model, $fields, $inverse = false)
    {
        foreach ($fields as $field) {
            $model->$field = (!$inverse) ? date('d.m.Y H.i', $model->$field) : strtotime($model->$field);
        }
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function fetchFromCsv(&$error)
    {
        $error = [];
        $csvData = self::fetchFromCsvFile();
        if (!$csvData) {
            $error []= 'Ошибка разбора файла или пустой файл';
            return false;
        }
        $headers = ['Код', 'Номенклатура', 'Ед. Изм.', 'Объем', 'Цена за ед', 'Состояние', 'Объект', 'Дата начала', 'Отображать на сайте', 'Направление'];
        $diff = array_diff(array_keys($csvData[0]), $headers);
        $diff2 = array_diff($headers, array_keys($csvData[0]));
        if (count($diff2)) {
            $error []= 'В файле отсутствуют необходимые колонки ('.implode(', ', $diff2).')';
        }
        if (count($diff)) {
            $error []= 'В файле лишние колонки ('.implode(', ', $diff).')';
        }
        if ($error) {
            return ;
        }

        $stat = [];
        foreach ($csvData as $line) {
        	$material = new Materials;
            $material->categoryId = 4;
            $material->name = $line['Нуменклатура'];
            $material->unit = $line['Ед. Изм.'];
            $material->volume = $line['Объем'];
            $material->price = $line['Цена за ед'];
            $material->object = $line['Объект'];
            $material->status = array_search($line['Состояние'], $material->statuses);
            $material->start = strtotime($line['Дата начала']);

            $material->published = $line['Отображать на сайте'];
            if (mb_strtolower($material->published) == 'да') {
            	$material->published = 1;
            }
            if (mb_strtolower($material->published) == 'нет') {
            	$material->published = 0;
            }

            $directions = explode(',', $line['Направление']);
            foreach ($directions as $k => $v) {
            	$v = trim($v);
                $dir = Directions::model()->findByAttributes(['name' => $v]);
                $directions [$k] = $dir->id;
            }

            $material->createdBy = yii::app()->user->id;
            $material->userId = Yii::app()->user->id;

            if ($material->validate() && $material->save()) {
                MaterialsDirections::model()->saveBatch(false, $directions);
            	$stat ['добавлено'] ++ ;
            } else {
	            $stat ['ошибок'] ++ ;
            }
        }
        return $stat;
    }

    function fetchFromCsvFile()
    {

        $filename = $_FILES['csv-file']['tmp_name'];

        $row = 1;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            $csvData = [];
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $csvData []= $data;
            }
            fclose($handle);
        }

        $header = array_shift($csvData);
        foreach ($header as $k => $v) {
        	$header [$k] = iconv('windows-1251', 'utf-8', $v);
            $header [$k] = trim(preg_replace('~\s+~i', ' ', $header [$k]));
        }

        $import = [];
        foreach ($csvData as $vals) {
            $row = [];
            foreach ($vals as $k => $v) {
            	$row [$header[$k]] = iconv('windows-1251', 'utf-8', $v);
            }
        	$import []= $row;
        }
        return $import;
    }

    function saveImages()
    {
        if (!$this->id) {
            return ;
        }
        $dir = 'images/materials';
        if (!file_exists($dir)) {
            mkdir($dir);
        }

        $maxW = 1920;
        $maxH = 1080;
        $images = CUploadedFile::getInstancesByName('images');
        foreach ($images as $file) {
            //echo '<br />'.$dir.'/'.$file->getName();
            $img = $dir.'/'.$file->getName();
            $file->saveAs($img);
            list($w, $h) = getimagesize($img);
            if ($w > $maxW) {
                $image = Yii::app()->image->load($img);
                $image->resize($maxW, $maxH)->quality(80);
                $image->save();
            }


            $file = new File;
            $file->object = 'materials';
            $file->id_item = $this->id;
            $file->path = $img;
            $file->date_added = date('Y-m-d H:i:s');
            $file->save();

        }
    }

    function getImages()
    {
        $a = File::model()->findAllByAttributes(['id_item' => $this->id, 'object' => 'materials']);
        $files = [];
        foreach ($a as $k => $v) {
            $preview = 'protected/runtime/preview';
            if (!file_exists($preview)) {
            	mkdir($preview);
            }
            $preview .= '/'.basename($v->path);
            if (!file_exists($preview)) {
                $image = Yii::app()->image->load($v->path);
                $image->resize(100, 100)->quality(70);
                $image->save($preview);
            }

        	$files [$v->id]= $preview;
        }
        return $files;
    }

    function removeImages($id=false)
    {
        $a = File::model()->findAllByAttributes(['id_item' => $id ?: $this->id, 'object' => 'materials']);
        $files = [];
        foreach ($a as $k => $v) {
            $preview = 'protected/runtime/preview';
            $preview .= '/'.basename($v->path);
            if (file_exists($preview)) {
                unlink($preview);
            }
            if (file_exists($v->path)) {
            	unlink($v->path);
            }
            $v->delete();
        }
        return $files;
    }
}
