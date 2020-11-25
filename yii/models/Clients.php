<?php

class Clients extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{clients}}';
    }

    public function rules()
    {
        return array(
            array('birthday,name,phone,id_agency', 'safe')
        );
    }

    public function relations()
    {
        return array(
            'agency' => array(self::BELONGS_TO, 'Agency', 'id_agency'),
            'user' => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name' => 'Имя',
            'phone' => 'Телефон',
            'phone_st' => 'Телефон стац.',
            'email' => 'Емейл',
            'skype' => 'Скайп',
            'birthday' => 'День рождения',
            'comment' => 'Комментарий',
            'scheme' => 'Схема',
            'id_agency' => 'Агентство',
        );
    }

    /**
     * Добавляет/обновляет клиента на основе данных $data ($_POST) из формы
     */
    function updateOrInsert($data)
    {
        // Сохраняем данные клиента
        if ($data['id_client']) {
            $model = Clients::model()->findByPk($data['id_client']);
        } else if ($data['name']) {
            $cond = 'name="'.addslashes($data['name']).'" AND email LIKE "'.$data['email'].'"';
            $model = Clients::model()->find(array(
                'condition' => $cond
            ));
            if (!$model) {
            	$model = new Clients;
            } else {
                return $model;
            }
        } else {
            $model = new Clients;
        }

        // История начало
        $history = $model->id ? 'edit' : 'add';
        $lastValues = array();
        if ($model->id) {
            foreach ($model as $k => $v) {
            	$lastValues [$k]= $v;
            }
        }

        // Заполняем данные
        if (!isset($data['id_user'])) {
        	$data['id_user'] = Yii::app()->user->id;
        }
        $model->id_user  = $data['id_user'];
        $model->name     = $data['name'];
        $model->comment  = $data['comment'];
        if (intval($data['birthday']) > 0) {
        	$model->birthday = $data['birthday'];
        }
        $model->id_agency = $data['id_agency'];
        if ($data['date_added']) {
        	 $model->date_added = $data['date_added'];
        } elseif (!$model->id) {
        	 $model->date_added = date('Y-m-d H:i:s');
        }
        // Сохраняем контакты
        if ($data['phone']) {
        	$model->phone = $data['phone'];
        }
        if ($data['phone_st']) {
        	$model->phone_st = $data['phone_st'];
        }
        if ($data['phone_extra']) {
        	$model->phone = $data['phone_extra'];
        }
        if ($data['phone_home']) {
        	$model->phone_home = $data['phone_home'];
        }
        if ($data['email']) {
        	$model->email = $data['email'];
        }
        if ($data['skype']) {
        	$model->skype = $data['skype'];
        }

        // Проверка сохранение
        if (!$model->validate()) {
            echo strip_tags(CHtml::errorSummary($model));
            return false;
        }
        $model->save();


        // Добавить лог в историю
        if ($history == 'add') {
        	$comment = 'добавлен';
        } else {
        	$comment = 'изменены поля:';
            if ($lastValues) {
                $labeles = Clients::attributeLabels();
                $changed = array();
                foreach ($lastValues as $k => $v) {
                    if ($model->$k != $v) {
                    	$changed []= ''.($labeles[$k] ? $labeles[$k] : $k).'='.$model->$k.'';
                    }
                }
                $comment .= ' '.implode(', ', $changed);
            }
        }
        History::addHistory('client', $history, $model->id, $comment, 0, 0, Yii::app()->user->id);

        return $model;
    }
            
            
    /**
     * Справочник привязок клиентов имя=>id по текущему юзера
     *   если type='agency', то возвращается привязка id=>id_agency
     */
    public static function getAssocByUser($type='') {
        static $clients;
        if ($clients) {
            return $clients;
        }
        $клиенты = Clients::model()->findAll(array(
            'condition' => 'id_user='.Yii::app()->user->id
        ));
        $clients = array();
        foreach ($клиенты as $k => $v) {
            if ($type == 'agency') {
                $clients [$v->id]= $v->id_agency;
            } else {
                $clients [$v->name]= $v->id;
            }
        }
        return $clients;
    }

    /**
     * Все клиенты текущего юзера
     */
    public static function getAllByUser($order='t.id desc')
    {
        static $clients;
        if ($clients) {
            return $clients;
        }
        $ids = Clients::getIdsFromCabinet();
        // Далее все клиенты из таблицы + первый массив
        $add = '';
        if ($ids) {
        	$add = ' OR t.id IN ('.implode(',', $ids).')';
        }
        $clients = Clients::model()->with('agency')->findAll(array(
            'condition' => '(t.id_user='.Yii::app()->user->id.' AND t.id_parent is null)'.$add,
            'order' => $order
        ));
        return $clients;
    }

    /**
     * Все идс клиентов из объявлений в кабинете
     */
    function getIdsFromCabinet()
    {
        // Сначала
        $ids = array();
        $sql = 'select client_id from zz_objects where client_id > 0 AND id_user='.Yii::app()->user->id.'';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $ids []= $v['client_id'];
        }
        return $ids;
    }

    /**
     * Вернуть указанного клиента по ID текущего юзера
     */
    public function getClient($id)
    {
        $ids = Clients::getIdsFromCabinet();
        $params = array(
            'id' => $id
        );
        if (!in_array($id, $ids)) {
        	$params ['id_user']= Yii::app()->user->id;
        }
        $client = Clients::model()->findByAttributes($params);
        return $client;
    }

    /**
     * Селектор выбора клиентов текущего юзера
     */
    public static function selector($selected='')
    {
        $data = Clients::getAllByUser();
        $clients = array(
            '' => 'выбрать клиента'
        );
        foreach ($data as $k => $v) {
            $clients [$v->id]= $v->phone.' - '. $v->name.' - '. $v->agency->name;
        }
        $html = CHtml::dropDownList('client_id', $selected, $clients, array('class' => 'form-control', 'style' => 'max-width:300px'));
        echo $html;
    }

    /**
     * Возвращает текущего выбранного клиента текущего юзера
     */
    public function get()
    {
        static $client;
        if (isset($client)) {
            return $client;
        }
        $id_client = $_COOKIE['id_client'];
        if (!$id_client || !Yii::app()->user->id) {
            return '';
        }
        $user = User::getMe();
        $attrs = array('id' => $id_client);
        if ($user->id_agency != 17) {
        	$attrs ['id_user']= Yii::app()->user->id;
        }
        $client = Clients::model()->findByAttributes($attrs);
        return $client;
    }
    
    /**
     * Очистить текущего выбранного клиента текущего юзера
     */
    public function clearSaved()
    {
        unset($_COOKIE['id_client']);
        setcookie('id_client', '', 0, '/');
    }

    /**
     * Удалить клиента по ид и ид юзера
     */
    public function remove($id_client, $id_user)
    {
        $client = Clients::model()->findByAttributes(array('id' => $id_client, 'id_user' =>
            $id_user));
        if (!$client) {
            return ;
        }
        $res = $client->delete();
        if (!$res) {
            echo 'Ошибка удаления клиента';
        }
    }
    
    
    
    /**
     * Метод, который рандомно выбирает один или несколько итемов где contact или client_phone = $phone причем непонятно, какое же все-таки поле равно $phone
     */
    public function getItemsByPhone($phone, $func='find')
    {
        $wh = Contacts::getByPhoneCondition($phone);
        $wh1 = str_replace('phone', 'contact', $wh);
        $wh2 = str_replace('phone', 'client.phone', $wh);
        $item = Objects::model()->with('client')->$func(array(
            'condition' => ' ('. $wh1 .') OR ('. $wh2 .')',
            'limit' => 50 // чтобы не перегружать в случае множественной выборки
        ));
        return $item;
    }

    /**
     * Возвратить все связанные телефоны по данному номеру телефона с привязкой к именам
     */
    public function getClientPhonesNames($phone)
    {
        $wh = Contacts::getByPhoneCondition($phone);
        $wh1 = str_replace('phone', 'contact', $wh);
        $wh2 = str_replace('phone', 'client.phone', $wh);
        $where = ' ('. $wh1 .') OR ('. $wh2 .')';
        $items = Objects::model()->with('client')->findAll(array(
            'condition' => $where,
            'limit' => 10,
        ));

        $allPhones = Contacts::getAllPhones($phone);
        $phones = array();
        foreach ($items as $k => $v) {
            $ph = $name = '';
            foreach ($allPhones as $phone) {
                if ($v->client->phone && strpos($v->client->phone, $phone) !== false) {
                	$ph = $v->client->phone;
                    $name = $v->client->name;
                    break;
                }
                if ($v->contact && strpos($v->contact, $phone) !== false) {
                	$ph = $v->contact;
                    $name = $v->name;
                    break;
                }
            }
            if (!$ph) {
                continue;
            }
            $phones []= array($ph, $name);
        }
        return $phones;
    }

    /**
     * Возвращает клиента по номеру телефона и имени
     */
    public function getClientByPhone($phone, $username='')
    {

        if (strlen($phone) < 2 && strlen($username) < 2) {
            return false;
        }

        // 2. Искать кол-во объявлений, привязанных к клиенту.

        // Поиск пользователей по условию
        /*$cond = array();
        $firstname = $_GET['firstname'];
        $lastname = $_GET['lastname'];
        if ($firstname && $lastname) {
            $cond []= '(name LIKE "'.$firstname.' '.$lastname.'")';
        }*/

        $q = Contacts::getByPhoneCondition($phone);
        //$cond []= $q;

        $sql = 'select * from zz_clients where id_user='.Yii::app()->user->id.' and ('.$q.')';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        if ($data) {
            return $data[0];
        }


        return array();

        /*$agency = $_POST['agency'];
        if ($agency) {
            $cond []= 'id_agency = ' . $agency;
        }*/
        
        // Сначала добавляем к условию where на id_контакты
        /*if ($phone) {
            $data = Contacts::getByPhone($phone);
            $idContact = array();
            foreach ($data as $k => $v) {
               $idContact []= $v->id;
            }
            //echo ' idc='.count($idContact).' ';
            if (count($idContact)) {
            	$cond []= 'id_contact IN ('.implode(', ', $idContact).')';
            }
        }*/


        // Ищем итемы по номеру, если есть - создаем и возвращаем
       /* $data = array();
        if (!$cond) {
            $items = Clients::getItemsByPhone($phone, $func='findAll');

            $allPhones = Contacts::getAllPhones($phone);
            $phone = $idAgency = $type = '';
            foreach ($items as $k => $v) {
                $ph = $name = '';
                foreach ($allPhones as $phone) {
                    if ($v->client->phone && strpos($v->client->phone, $phone) !== false) {
                        if (!$name) {
                        	$name = $v->client->name;
                        }
                        if (!$phone) {
                        	$phone = $v->client->phone;
                        }
                        if (!$idAgency) {
                        	$idAgency = $v->client->id_agency;
                        }
                        $type = 'client';
                        break;
                    }
                    if ($v->contact && strpos($v->contact, $phone) !== false) {
                        if (!$name) {
                        	$name = $v->name;
                        }
                        if (!$phone) {
                        	$phone = $v->contact;
                        }
                        if (!$idAgency) {
                        	$idAgency = $v->id_agency;
                        }
                        $type = 'user';
                        break;
                    }
                }
            }

            if ($phone) {
            	return array(
                    'name' =>  $name,
                    'id_agency' => $idAgency,
                    'id' => $item->id,
                    'phone' => $phone,
                    'phone_extra' => "",
                    'phone_st' => "",
                    'type' => $type
                );
            }
        }*/



        $q = Contacts::getByPhoneCondition($phone);
        $q = str_replace('phone', 'c.phone', $q);
        $w = array(
            $q
        );
        if ($cond) {
        	$w []= implode(' AND  ', $cond);
        }

        $sql = 'SELECT name, id_agency, id, phone, "client" AS type
            FROM {{clients}} WHERE ('.implode(') OR  (', $w).')';

        $item = Yii::app()->db->createCommand($sql)->query()->readAll();
        if (count($item)) {
            $data = array_merge($data, $item);
        }
        
        if ($cond) {
            $sql = 'SELECT CONCAT(lastname, " ", firstname) AS name, id_agency, id, phone, phone_extra,
                phone_st, "user" AS type FROM {{user}} WHERE '.implode(' AND  ', $cond).'';
            $item = Yii::app()->db->createCommand($sql)->query()->readAll();
            if (count($item)) {
                $data = array_merge($data, $item);
            }
        }

        if (!count($data)) {
            return false;
        }

        $selected = $data[0];
        foreach ($data as $k => $v) {
            if ($v['name'] == $username) {
            	$selected = $data[$k];
            }
        }

        return $selected;
    }

    /**
     * Условие по телефону и клиента
     */
    public function getConditionByPhone($phone, $item)
    {

        $cond = array();
        /*$phone = $_GET['phone'];
        if ($phone) {
            $phone = str_replace('-', '', $phone);
            $cond []= 'contact LIKE "'.$phone.'" OR client_phone LIKE "'.$phone.'"';
            if (strlen($phone) == 6) {
            	$phone = substr($phone, 0, 2).'-'.substr($phone, 2, 2).'-'.substr($phone, 4, 2);
            }
        }*/

        if (count($idContact)) {
        	/// $cond []= 'id_contact IN ('.implode(', ', $idContact).')';
        }

        //echo '<pre>'; print_r($item); echo '</pre>';

        $phone = $item['phone'];
        if (!$phone) {
        	$phone = $item['phone_extra'];
        }
        if (!$phone) {
        	$phone = $item['phone_st'];
        }

        $q = Contacts::getByPhoneCondition($phone);
        if ($q && $q != 1) {
            $q = str_replace('phone', 'client.phone', $q);
        	$cond []= ' ('.$q.') ';
            $q = str_replace('client.phone', 'contact', $q);
        	$cond []= ' ('.$q.') ';
        }

        if ($item['id']) {
        	 $cond []= 'client_id='.$item['id'].'';
        }

        $c = '';
        if ($cond) {
            //`t`.status="confirm" ' .' AND
            $c = ' ' .implode(' OR ', $cond);
        }
        return $c;
    }
    
    public function countClientAdvers($phone)
    {
        if (empty($phone)) {
            return array();
        }
        $item = Clients::getClientByPhone($phone);
        if (!$item['id']) {
            return array();
        }
        
        $countAll = Objects::model()->count(array(
            'condition' => 'client_id='.intval($item['id']).''
        ));
        
        return $countAll;
    }

    /**
     * Все объявления клиента
     */
    public function getClientAdvers($phone, $addWhere='', &$error='')
    {

        if (empty($phone)) {
            return array();
        }
        $item = Clients::getClientByPhone($phone);
        if (!$item['id']) {
            return array();
        }
        /*$cond = Clients::getConditionByPhone($phone, $item);
        

        $wh = Contacts::getByPhoneCondition($phone);
        $wh1 = str_replace('phone', 'contact', $wh);
        $wh2 = str_replace('phone', 'client.phone', $wh);

        $c = array();
        if ($wh1 && $wh1 != 1) {
        	$c []= '('.$wh1.')';
        }
        if ($wh2 && $wh2 != 1) {
        	$c []= '('.$wh2.')';
        }
        $cond2 = implode(' OR ', $c);

        if ($cond) {
        	$cond = $cond;
            if ($cond2) {
            	$cond .= ' OR '.$cond2;
            }
        } else {
            $cond = $cond2;
        }

        if ($_GET['id_nedv']) {
        	$cond = '('.$cond.') AND id_nedv='.intval($_GET['id_nedv']);
        }
        if (array_key_exists('isSell', $_GET)) {
            if ($_GET['isSell']) {
            	$cond .= ' AND (`t`.id_nedv=1 OR `t`.id_nedv=4)';
            } else {
            	$cond .= ' AND (`t`.id_nedv=3 OR `t`.id_nedv=5)';
            }
        }

        if (!$cond) {
        	$error = 'Пустое условие поиска advers по номеру '.$phone.'';
            return array();
        }

        if ($addWhere) {
        	$cond = '('.$cond.') AND ('.$addWhere.')';
        }*/

        $objects = Objects::getAll(array(
            'condition' => 'client_id='.intval($item['id']).'',
            'order' => 't.id_nedv, t.id_type DESC',
            'limit' => 50,
            'offset' => 0
        ), $cash=3600, $idCache='', $config=array(
            'noReplace' => 1
        ));

        if (count($objects) > 1000) {
            Yii::log('Ошибка getClientAdvers найдено '.count($objects).' объявлений по номеру '.$phone, 'error');
            return array();
        }

        /*$items = $this->getItemsByPhone($phone, $func='findAll');
        foreach ($items as $k => $v) {
        	$objects []= array('item' => $v);
        }*/
        return $objects;
    }

    /**
     * Может ли $user видеть клиента объявления $item
     */
    public function canViewClient($user, $item)
    {
        if ($item->id && !$_REQUEST['spros']) {
            $allow = $item->getClientAllow($user);
            if (!$allow) {
                return false;
            }
        }
        return true;
    }

    /**
     * Явлется ли клиент агентством
     */
    public function isAgency()
    {
        return $this->id_agency > 0 && $this->id_agency != 10001;
    }
    
    /**
     * Проверка, заблокирован ли телефон клиента (с ним кто-то уже работает)
     */
    public function checkClientPhone($phone, &$error)
    {
        $phone = preg_replace('~[^\d]~i', '', $phone);

        if (strlen($phone) < 6) {
            // $error = 'Телефон клиента указан неверно';
            return false;
        }

        $where = 't.status NOT IN ("'.implode('", "', Objects::removedStatuses()).'")';
    	$advers = Clients::getClientAdvers($phone, $where, $error);
        $owner = $ownerItem = $owner2Item = '';
        foreach ($advers as $k => $v) {
            if ($v['item']->id_user && $v['item']->id_user != Yii::app()->user->id) {
                if ($owner) {
                    if ($owner2Item) {
                    	break;
                    }
                    if ($v['item']->id_user != $ownerItem->id_user) {
                    	$owner2Item = $v['item'];
                    }
                    continue;
                }
            	$owner = $v['item']->id_user;
            	$ownerItem = $v['item'];
            }
        }
        if ($owner && $owner != Yii::app()->user->id) {
            $more = '';
            if ($owner2Item) {
            	$more = ', а также '.$owner2Item->user->fullname().'';
            }
            $error = 'С этим клиентом (тел. '.$phone.') уже работает '.
                $ownerItem->user->fullname().' (лот '.$ownerItem->id.') '.$more.' '.
                'поэтому нельзя добавлять такое объявление';
            Yii::log('Блокировка клиента '.$phone.' для юзера '.Yii::app()->user->id, 'error');
            return false;
        }
        return true;
    }

    /**
     * Статистика объявлении по клиентам
     */
    public function getObjectsStat($ids)
    {
        // Статистика объявлений
        $stat = array();
        $sql = 'select count(*) as c, client_id from zz_objects where client_id IN ('.implode(',', $ids).
            ') group by 2';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $stat [$v['client_id']]= $v['c'];
        }
        return $stat;
    }

    /**
     * Адреса объявлений по клиентам
     */
    public function getObjectAddresses($ids)
    {
        // Статистика объявлений
        $stat = array();
        $sql = 'select IF(house,CONCAT(street, " д.", house),street) AS street, client_id from zz_objects where client_id IN ('.implode(',', $ids). ') group by 2';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $stat [$v['client_id']]= $v['street'];
        }
        return $stat;
    }

    /**
     * Статистика истории по клиентам
     */
    function getHistoryStat($ids)
    {
        // История клиентов
        $history = array();
        $sql = 'select count(*) as c, id_item from zz_history where model="client" AND id_item IN ('.implode(',', $ids). ') group by 2';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $history [$v['id_item']]= $v['c'];
        }
        return $history;
    }

    /**
     * История по клиенту
     */
    public function getHistory($clientId)
    {
        // История клиентов
        $history = array();
        $sql = 'select * from zz_history where model="client" AND id_item='.$clientId.' ORDER BY id desc';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $history []= $v;
        }
        return $history;
    }


    /**
     * Данные по договорам с клиентами
     */
    public function getDogovors()
    {
        $dogovors = array(
            'pokupatel_razov' => 'Договор разового показа',
            'pokupatel_avans' => 'Договор аванса с покупателем',
            'prodavec_agent' => 'Договор с продавцом агентсткий',
            'prodavec_exclusive' => 'Договор на продавца эксклюзив',
        );
        return $dogovors;
    }

    /**
     * Путь к ртф версии договора
     */
    function getDogovorRtf($type)
    {
        return 'protected/data/dogovor_'.$type.'.rtf';
    }

    /**
     * Титл договора
     */
    public function getDogovorTitle($dogovor_type)
    {
        $dogovors = Clients::getDogovors();
        $titleDogovor = $dogovors[$dogovor_type];
        return $titleDogovor;
    }
}


