<?php

class Contacts  extends CActiveRecord
{

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{contacts}}';
    }

    public function rules()
    {
        return array();
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
            'id_agency' => 'Агентство',
        );
    }

    /**
     * Источник
     */
    function getSource()
    {
        if ($this->source == 'edit') {
            return 'Информация от пользователя';
        } else {
            return $this->source;
        }
    }

    /**
     * getConfirmedPhones
     */
    function getConfirmedPhones($universal)
    {
        $confirmed = array();
        $sql = 'select * from zz_contacts where phone IN ("'.implode('","', $universal).'") AND source_level=1';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
        	$confirmed []= $v['phone'];
        }
        return $confirmed;
    }

    /**
     * Массив агентств на основе списка телефонов
     */
    function getPhonesAgency($universal)
    {
        $ids = array();
        $sql = 'select id_agency from zz_contacts where phone IN ("'.implode('","', $universal).'")';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
        	$ids []= $v['id_agency'];
        }
        return $ids;
    }

    /**
     * Метод, который бы на основе привязок телефон-агентство возвращает ид агентства
     */
    function getAgency($item)
    {
        if (!$item->contact || $item->contact == '-') {
            return false;
        }
        $universal = Contacts::universalAll($item->contact, 1);
        if (!$universal) {
            return false;
        }
        $ags = Contacts::getPhonesAgency($universal);
        $agency = false;
        foreach ($ags as $k => $v) {
            if ($v > 0) {
            	$agency = $v;
            }
        }
        // Проверяем, существует ли вообще такое агентство
        if ($agency > 0) {
        	$name = Agency::getName($agency);
            if (!$name || $name == '#'.$agency) {
                return false;
            }
        }
        
        return $agency;
    }

    /**
     * Ручное указание агентства
     */
    function setPhoneAgency($phone, $id_agency, $name='', $comment='')
    {
        $phones = explode(',', preg_replace('~[^\d,]~i', '', $phone));
        if ($phones) {
            $id_agency = intval($id_agency);

            $sql = 'select id, id_agency from zz_contacts WHERE phone IN ('.implode(',', $phones).')';
            $data = Yii::app()->db->createCommand($sql)->query()->readAll();
            foreach ($data as $k => $v) {
                $id_contact = $v['id'];
                $h = new History;
                $h->model = 'contacts';
                $h->action = 'edit';
                $h->id_item = $id_contact;
                $h->date_added = date('Y-m-d H:i:s');
                $h->id_user = Yii::app()->user->id;
                $h->comment = 'Агентство изменено с '.Agency::getName($v['id_agency']).' на '.
                    Agency::getName($id_agency).'';
                $h->save();
            }

            $set = 'id_agency='.$id_agency.', source="edit", source_level=1,
                id_user='.Yii::app()->user->id.', date_changed="'.date('Y-m-d H:i:s').'"';
            if ($name) {
            	$set .= ', name="'.addslashes($name).'"';
            }
            if ($comment) {
            	$set .= ', comment="'.addslashes($comment).'"';
            }

            $sql = 'update zz_contacts set '.$set.' WHERE phone IN ("'.implode('","', $phones).'")';
            Yii::app()->db->createCommand($sql)->query();
        }
    }

    /**
     * Добавление нового телефона в базу без информации
     */
    function addPhoneEmpty($phone)
    {
        $contact = new Contacts;
        $contact->phone = $phone;
        $contact->id_user = Yii::app()->user->id;
        $contact->date_added = date('Y-m-d H:i:s');
        $contact->save();
        return $contact;
    }

    /**
     * Метод для добавления привязки телефон-агентство в базу
     *
     * @array Параметры
     * @string Отладочная информация
     *
     * Contacts::addPhone($phone, $id_agency, $name);
     */
    function addPhone($p, &$info='')
    {
        static $existPhones;
        if (!isset($existPhones)) {
            $existPhones = array();
            $sql = 'select id, phone from zz_contacts';
            $data = Yii::app()->db->createCommand($sql)->query()->readAll();
            foreach ($data as $k => $v) {
                $existPhones [$v['id']]= $v['phone'];
            }
        }
        extract($p);

        if ($item) {
            $id_agency = Objects::getItemAgency($item);
            $name = $item->name;
            $src = $item->getSourceInfo();
            $source = $src['alias'];
        }

        /*if (!$id_agency) {
            $info = 'Телефон '.$phone.' не расшифрован - не указано агентство или иное';
            return false;
        }*/

        $contact = new Contacts;
        $contact->phone = $phone;
        $contact->id_agency = $id_agency;
        $contact->id_user = Yii::app()->user->id;
        $contact->date_added = date('Y-m-d H:i:s');
        if ($name) {
        	$contact->name = $name;
        }
        if ($source) {
        	$contact->source = $source;
        }
        if ($comment) {
        	$contact->comment = $comment;
        }
        if (!$level) {
        	$level = 3;
        }
        if ($level) {
        	$contact->source_level = $level;
        }

        // Если телефон уже есть в базе, то добавляем в историю
        if (in_array($phone, $existPhones)) {
            $contact->id = array_search($phone, $existPhones);

            $res = '';
            $res = Contacts::addHistory($contact, $item);
            $info = 'Телефон уже есть в базе '.$phone;
            if ($res) {
            	$info .= '++';
            } else {
            	$info .= '--';
            }

            Contacts::updatePhone($contact, $contact->id, $info);

            return false;
        }

        $info = '++';
        //return ;

        if (!$contact->save()) {
            error('Ошибка сохранения контакта: '.CHtml::errorSummary($contact));
            return false;
        }

        Contacts::addHistory($contact, $item);

        $existPhones []= $phone;
        return true;
    }

    /**
     * Обработка и регистрация в базе контактов объявления
     */
    function processItem($item)
    {
        $a = $item->getSourceInfo();

        $phones = Contacts::universalAll($item->contact);

        foreach ($phones as $phone) {
        	Contacts::addPhone(array(
                'phone' => $phone,
                'item' => $item,
                'alias' => $a['alias']
            ), $info);
        }
        return $phones;
    }

    /**
     * Дозаполнение
     * - $contact - это новый контакт, его данные
     * - $id_current - это текущий найденный в базе существующий (его надо обновить)
     */
    function updatePhone($contact, $id_current, &$info='')
    {
        $contactCurrent = Contacts::model()->findByPk($id_current);

        // Агентство
        $changeAgency = 0;
        if ($contact->id_agency && $contact->id_agency != $contactCurrent->id_agency) {
            // Если А не указано, либо частное лицо - значит надо дозаполнить в любом случае
            if ($contactCurrent->id_agency == 10001 || !$contactCurrent->id_agency) {
            	$info .= ' * '.$contactCurrent->id_agency.' ('.$contactCurrent->source.') => '.
                    $contact->id_agency.' ('.$contact->source.')';
                $changeAgency = 1;
            } else {
                // Если А пришло другое - это самая сложная ситуация
                // На Чл менять не будем вообще
                if ($contact->id_agency == 10001) {

                // С А на А пусть меняется, все равно
                } else {
                	$info .= ' ---- '.$contactCurrent->id_agency.' ('.$contactCurrent->source.') => '.
                        $contact->id_agency.' ('.$contact->source.')';
                    $changeAgency = 1;
                }
            }
        }

        // Имя
        // Имя это малоинформативное поле и обновлять его бесполезно. Под одним телефоном могут выставляться
        // разные имена Городские телефоны например. Даже мобильные и те могут быть общими.

        // Сохраняем
        $save = 0;
        if ($changeAgency) {
        	$save = 1;
            $contactCurrent->id_agency = $contact->id_agency;
        }
        if ($save) {
        	$contactCurrent->save();
        }
    }

    /**
     * Сохранение истории телефона
     */
    public function addHistory($contact, $item)
    {
        // В авторежиме пишем
        if ($item) {
        	Contacts::addPhoneItemHistory($contact, $item);
        } else {
            Contacts::addPhoneHistory($contact);
        }
    }

    /**
     * Добавление в лог информации о привязанном к телефону объявлении
     * id_contact всегда пишется в id_item, т.к. является экземпляром модели Contacts
     */
    function addPhoneItemHistory($contact, $item)
    {
        /*if (Contacts::existPhoneItemHistory($contact->id, $item->id)) {
            return ;
        }*/
        $h = new History;
        $h->model = 'contacts';
        $h->action = 'item';
        $h->id_item = $contact->id;
        $h->uniq = $item->id;
        $h->date_added = date('Y-m-d H:i:s');
        $h->id_user = Yii::app()->user->id;
        $h->comment = $item->getTitleSimple();
        $src = $item->getSourceInfo();
        $data = array(
            'date' => $item->date_added,
            'agency' => $item->id_agency,
            'contact' => $item->contact,
            'name' => $item->name,
            'source' => $src['alias']
        );
        $h->content = serialize($data);
        $res = $h->save();
        return $res;
    }

    /**
     * Добавлена ли уже такая запись в историю (2 способа - либо сразу извлекать все связки, либо каждый раз запросы)
     */
    /*public function existPhoneItemHistory($id_contact, $id_item)
    {
        static $contactItems;
        if (!isset($contactItems)) {
        	$contactItems = array();
            $sql = 'select id_item, uniq from zz_history where model="contacts" and action="item"';
            $data = Yii::app()->db->createCommand($sql)->query()->readAll();
            foreach ($data as $k => $v) {
                $contactItems []= $v['id_item'].'-'.$v['uniq'];
            }
        }
        return in_array($id_contact.'-'.$id_item, $contactItems);
    }*/

    /**
     * История телефона забивается по факту добавления нового или обнаружения уще имеющегося
     */
    function addPhoneHistory($contact)
    {
        $h = new History;
        $h->model = 'contacts';
        $h->action = 'add';
        $h->id_item = $contact->id;
        $h->uniq = $contact->id_agency;
        $h->date_added = date('Y-m-d H:i:s');
        $h->id_user = Yii::app()->user->id;
        $data = array();
        $add = explode(' ', 'source source_level comment name');
        foreach ($add as $k) {
            if ($contact->$k) {
            	$data [$k]= $contact->$k;
            }
        }
        $h->content = serialize($data);
        $res = $h->save();
        return $res;
    }

    /**
     * История телефона
     */
    function getHistory($id_contact)
    {
        $sql = 'select * from zz_history where model="contacts" and id_item='.$id_contact.'
           AND uniq>0 ORDER BY date_added LIMIT 100';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();

        if (!$data) {
            return array();
        }

        $ids = array();
        foreach ($data as $a) {
            $ids []= $a['uniq'];
        }
        $sql = 'select id, status from zz_objects t where id IN ('.implode(',', $ids).')';
        $a = Yii::app()->db->createCommand($sql)->query()->readAll();

        $existIds = array();
        foreach ($a as $a) {
            $existIds [$a['id']]= $a['status'];
        }

        foreach ($data as $k => $v) {
            if ($existIds[$v['uniq']]) {
            	$data [$k]['item-status'] = $existIds[$v['uniq']];
            }
        }
        
        return $data;
    }

    /**
     * Поиск по телефону
     */
    public function byPhone($phone)
    {
        return Contacts::model()->find(array(
            'condition' => 'phone="'.$phone.'"'
        ));
    }

    /**
     * Очистить все контакты
     */
    function clearAllContacts($onlyItem)
    {
        if ($onlyItem) {
            $sql = 'delete from zz_contacts where source != "База агентств" OR source is null';
            Yii::app()->db->createCommand($sql)->query();
        } else {
            $sql = 'truncate zz_contacts';
            Yii::app()->db->createCommand($sql)->query();
        }

        $sql = 'delete from zz_history where model="contacts"';
        if ($onlyItem) {
        	$sql .= ' AND action="item"';
        }
        Yii::app()->db->createCommand($sql)->query();
    }














    /**
     * Проверка на правильный номер телефона
     */
    static function checkPhone($v)
    {
        $v = str_replace('-', '', trim($v));
        $res = preg_match('~^(\+?(7|8)?(8|9)\d{9}|(\+?78212)?\d{6}|82\d{8})$~', $v);
        return $res;
    }

    /**
     * Проверка на емейл
     */
    static function checkEmail($email)
    {
        $res = preg_match('~[a-z_.\d-]+@[a-z_.\d-]+~i', $email);
        return $res;
    }

    /**
     * Условие на поиск по номеру телефона
     */
    public function getByPhoneCondition($phone)
    {
        $phone = trim($phone);
        $phones = Contacts::getAllPhones($phone);
        $q = array();
        foreach ($phones as $k => $v) {
            if ($v == '' || empty($v) || strlen($v) < 4) {
                continue;
            }
            $q []= 'REPLACE(REPLACE(REPLACE(phone, " ", ""), "+", ""), "-", "") LIKE "'.$v.'%" OR phone LIKE "'.
                $v.'%"';
        }
        $q = implode(' OR ', $q);
        if (!$q) {
        	$q = 1;
        }
        return $q;
    }

    /**
     * Все написания телефона. Почти идентичные функции с getAllPhones
     */
    public function getPhoneAliases($phoneOriginal)
    {

        // Первое - это сам телефон
        $phones = array($phoneOriginal);

        $phoneForAlias = $phoneOriginal;

        // Если это длинный номер
        if (strlen($phoneOriginal) > 6) {
            // Пытаем добавить алиасы 8* 7* 8212*
        	$phoneForAlias = preg_replace('~^8212~i', '', $phoneOriginal);
            $phones []= $phoneForAlias;
        	$phone = preg_replace('~^8~i', '7', $phoneOriginal);
            $phones []= $phone;
        	$phone = preg_replace('~^7~i', '8', $phoneOriginal);
            $phones []= $phone;
        }

        // Ищем обратный алиас - для короткого длинный. Для длинного короткий
        $phonealias = Utils::getPhoneAlias($phoneForAlias);
        $phonealias = str_replace('+', '', $phonealias);
        if ($phonealias && $phonealias != $phoneOriginal) {
        	$phones []= $phonealias;
        }
        if ($phonealias && strlen($phonealias) > 6) {
            $phones []= substr($phonealias, 1);
        	$phonealias = 8 . substr($phonealias, 1);
            $phones []= $phonealias;
        }
        if (strlen($phone) > 6) {
        	$a = substr($phone, 0, 1);
            $phones []= substr($phone, 1);
        	$phonealias = ($a == 7 ? 8 : 7) . substr($phone, 1);
            $phones []= $phonealias;
        }
        $phones = array_unique($phones);
        return $phones;
    }

    /**
     * Более расширенная версия, возвращает для коротких номеров все 8212xxx версии
     */
    function getAllPhones($phone)
    {
        $phones = array($phone);
        $phone       = preg_replace('~[-\s+]+~', '', $phone);
        $phoneAlias  = Utils::getPhoneAlias($phone);
        $phoneAlias  = str_replace('+', '', $phoneAlias);
        $shortNumber = strlen($phone) == 6 ? $phone : (strlen($phoneAlias) == 6 ? $phoneAlias : '');
        if (!empty($phoneAlias) && strlen($phoneAlias) > 6) {
            $phoneAlias2  = 8 . substr($phoneAlias, 1);
        } else {
            if (strlen($phone) > 7) {
                if (strpos($phone, '8') === 0) {
                	$phoneAlias2  = 7 . substr($phone, 1);
                } else {
                    $phoneAlias2  = 8 . substr($phone, 1);
                }
            }
        }

        $phones []= $phone;
        if (!empty($phoneAlias)) {
        	$phones []= $phoneAlias;
        }
        if (!empty($phoneAlias2)) {
        	$phones []= $phoneAlias2;
        }

        $codes = array();
        foreach ($phones as $k => $v) {
            if (strlen($v) > 6) {
            	$codes []= substr($v, 1, 5);
            }
        }
        if (in_array(91286, $codes) && strlen($phoneAlias) > 6) {
        	$phones []= str_replace('91286', '82127', '7'.substr($phoneAlias, 1));
        	$phones []= str_replace('91286', '82127', '8'.substr($phoneAlias, 1));
        }
        if ($shortNumber) {
        	$phones []= '88212'.$shortNumber;
        	$phones []= '78212'.$shortNumber;
        	$phones []= '8212'.$shortNumber;
        }

        $phones = array_unique($phones);
        return $phones;
    }

    /**
     * Обрезка телефона до ширины 6 цифры с сокрытием части "под кат"
     */
    function phoneCut($phone)
    {
        $phone = str_replace('-', '', $phone);
        if (strlen($phone) > 6) {
        	$phone = substr($phone, 0, 5) .'<a href="#" onclick="this.style.display=\'none\'; '.
                'this.nextSibling.style.display=\'inline\'; return false;">...</a><span style="display:none;">'.
                substr($phone, 4).'</span>';
        }
        return $phone;
    }

    /**
     * Форматирование номера телефона (либо списка телефонов) из строки в строку
     */
    public static function format($contact, &$firstPhone='')
    {
        $contact = str_replace(')', '', $contact);
        $contact = str_replace('(', ',', $contact);
        $phones = explode(',', $contact);
        $phones = array_slice($phones, 0, 2);
        $firstPhone = $phones[0];
        foreach ($phones as $k => $v) {
            if (strpos($v, '<') !== false) {
                continue;
            }
            $v = str_replace(' ', '', $v);
            if (mb_strlen($v) > 6) {
                $phones [$k] = preg_replace('~([78])(\d{3})(\d{2,3})(\d{2})(\d{2})~i', '$1 $2 $3-$4-$5', $v);;
            }
            if (mb_strlen($v) == 6) {
                $phones [$k] = preg_replace('~(\d{2})(\d{2})(\d{2})~i', '$1-$2-$3', $v);;
            }
        }
        return implode(', ', $phones);
    }
    
    /**
     * Разбивка строки телефонов в массив по отдельным номерам, и по каждому preparePhone
     */
    function phonesExplode($contacts)
    {
        $phones = Contacts::phonesExplodeOnly($contacts);
        $phs = array();
        foreach ($phones as $k => $v) {
            $pr = Contacts::preparePhone($v);
            if (!$pr) {
                continue;
            }
            $phs [$pr]= $pr;
        }
        return $phs;
    }

    /**
     * Разбивка строки телефонов в массив по отдельным номерам
     */
    public function phonesExplodeOnly($contacts)
    {
        $contacts = trim($contacts);
        $contacts = preg_replace('~[78]\s*(\(8212\))~i', '$1', $contacts);
        $contacts = str_replace(';', ',', $contacts);
        $contacts = str_replace('(', ',(', $contacts);
        if (strpos($contacts, ',') === 0) {
        	$contacts = substr($contacts, 1);
        }
        $contacts = str_replace('+', '', $contacts);
        $phones = explode(',', $contacts);
        foreach ($phones as $k => $v) {
            $v = trim($v);
            if (empty($v)) {
                unset($phones[$k]);
            }
        }
        return $phones;
    }

    /**
     * Разбор строки телефонов в массив универсальных телефонов. Это единая упрощенная функция для разбора контактов.
     */
    function universalAll($contacts, $assoc=0)
    {
        $phones = Contacts::phonesExplodeOnly($contacts);
        $phonesUniversal = array();
        foreach ($phones as $phone) {
            $phone = trim($phone);
            if (empty($phone)) {
                continue;
            }
            $universal = Contacts::universalPhone($phone);
            if (!$universal) {
                continue;
            }
            if ($assoc) {
                $phonesUniversal [$phone]= $universal;
            } else {
                $phonesUniversal []= $universal;
            }
        }
        return $phonesUniversal;
    }

    /**
     * Универсальное единое написание телефона
     */
    function universalPhone($phone, $pfx=8)
    {
        $phone = Contacts::preparePhone($phone);
        $phoneNew = $phone;
        if (strlen($phone) == 6) {
        	$phoneNew = Utils::getPhoneAlias($phone);
        }
        $phoneNew = str_replace('+', '', $phoneNew);
        if (strlen($phone) == 10) {
        	$phoneNew = $pfx.$phoneNew;
        } else {
            if ($pfx == 8) {
            	$phoneNew = preg_replace('~^7~i', $pfx, $phoneNew);
            } else {
            	$phoneNew = preg_replace('~^8~i', $pfx, $phoneNew);
            }
        }
        if (strlen($phoneNew) != 6 && strlen($phoneNew) != 11) {
            return ;
        }
        return $phoneNew;
    }

    /**
     * Получение правильного номера телефона из строки. Если он неправильный, возвращается пустота
     */
    function preparePhone($phone)
    {
        $replaces = array('\+', '78212', '212', '8212', '88212');
        $phone = trim($phone);
        if (empty($phone)) {
            return ;
        }
        if (!is_numeric($phone)) {
            $phone = preg_replace('~[-\+\(\)\s,]+~', '', $phone);
            if (!is_numeric($phone)) {
                //$sql []= $p.'=null';
                return false;
            } else {
                //$sql []= $p.'="'.$phone.'"';
            }
        }
        
        
        if (strlen($phone) > 8) {
        	$phone = preg_replace('~^('.implode('|', $replaces).')~i', '', $phone);
        }
        
        //if ($phoneAfter != $phone) {
            //$phone = ;
        	//$sql []= $p.'="'.$phone.'"';
        //}
        /*foreach ($replaces as $replace) {
            if (strpos($phone, $replace) === 0) {
                $phone = str_replace($replace, '', $phone);
                //$sql []= $p.'="'.$phone.'"';
            }
        }*/

        if (strlen($phone) < 6) {
            return false;
        }
        if (strlen($phone) > 12) {
            return false;
        }
        
        
        return $phone;
    }

    /**
     * Массив всех телефонов из всех таблиц (непонятно зачем)
     */
    /*function getMergedPhones()
    {
    
        static $phonesMerged;
        if (!isset($phonesMerged)) {
        	$phonesMerged = array();
        }

        // echo 'from='.Utils::formatSize(memory_get_peak_usage()).'';

        $phonesMerged = array();

        $sql = 'SELECT contact FROM {{agency}} WHERE contact is not null';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $phones = Contacts::phonesExplode($v['contact']);
            Contacts::addPhonesMerged($phones, $phonesMerged);
        }

        $sql = 'SELECT DISTINCT contact FROM {{objects}} WHERE contact is not null';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();
        foreach ($data as $k => $v) {
            $phones = Contacts::phonesExplode($v['contact']);
            Contacts::addPhonesMerged($phones, $phonesMerged);
        }

        $sql = 'SELECT id, phone, phone_extra, phone_st, phone_home FROM {{contacts}}';
        $data = Yii::app()->db->createCommand($sql)->query()->readAll();

        $ps = array('phone', 'phone_extra', 'phone_st', 'phone_home');
        foreach ($data as $k => $v) {
            $ph = array();
            foreach ($ps as $p) {
                $phone = trim($v[$p]);
                if (empty($phone)) {
                    continue;
                }
            	$ph []= $phone;
            }
            Contacts::addPhonesMerged($ph, $phonesMerged);
        }

        foreach ($phonesMerged as $k => $v) {
        	$phonesMerged [$k]= implode(',', $v);
        }
        //echo '<pre>'; print_r($phonesMerged); echo '</pre>';

        //echo '<br />end='.Utils::formatSize(memory_get_peak_usage()).'';

        return $phonesMerged;
    }
    

    function addPhonesMerged($phones, &$phonesMerged)
    {
        if (count($phones) < 2) {
            return ;
        }
        foreach ($phones as $phone) {
            //$t = implode(',', $phones);
            if (isset($phonesMerged [$phone])) {
                //echo '<br />--- '.$phonesMerged [$v].' - '.$t.'';
                // $phonesMerged [$phone] .= ','.$t;
                foreach ($phones as $k => $v) {
                    if (!in_array($v, $phonesMerged [$phone])) {
                        $phonesMerged [$phone] []= $v;
                    }
                }
            } else {
                $phonesMerged [$phone]= $phones;
            }
        }
    }*/

}

