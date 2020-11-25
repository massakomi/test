<?php

class Map
{

    /**
     * Вывод Легенды карты - обозначение значков, с помощью метода getImageByType
     */
    public static function insertLegend()
    {
        function printObjectTypeRow($types)
        {
            foreach ($types as $k => $v) {
                echo '<tr>';
                ?>
                <td title="<?=$k?>"><?=(!is_scalar($v) ? '<b>'.$v['name'].'</b>' : $v)?></td>

                <?php
                for ($i = 1;$i <= 4;$i ++) {
                    $to = ($i * 1000 - 1) * 1000;
                    list($href, $size) = Map::getImageByType($k, $to, false);
                    if ($href) {
                        echo '<td><img alt="" src="'.$href.'" /></td>';
                    } else {
                        echo '<td>-</td>';
                    }
                }
                echo '</tr>';

                if ($v['subs'] && is_array($v['subs'])) {
                    printObjectTypeRow($v['subs']);
                }
            }
        }
        ?>
            <div style="display:none;"><table border="0" cellspacing="0" cellpadding="3" id="mapLegendTable">
            <tr>
            <th>Тип объекта</th>
            <?php for ($i = 1;$i <= 4;$i ++) {
                if ($i == 4) {
                    echo '<th>Цена от 4000</th>';
                } else {
                    echo '<th>Цена до '.($i * 1000).'</th>';
                }
            } ?>
            </tr>
            <?php $types = ObjectTypes::getTree();printObjectTypeRow($types);?>
            </table></div>
        <?php
    }
    
    /**
     * Вставка универсальной Яндекс-Карты с указанным адресом, шириной, высотой
     */
    public function insert($address='', $width='', $height='', $slow=false, $tools=false, $objects='', $zoom=16, $callAfterLoad='')
    {
        if ($_COOKIE['dev']) {
            // return ;
        }
        if (!$height) {
            $height = $width * 0.6;
        }
        if ($tools === false) {
        	$tools = 'mapTools,typeSelector,zoomControl,smallZoomControl,scaleLine';
            // searchControl
        }
        if (!is_array($tools)) {
        	$tools = explode(',', $tools);
        }
        if (is_numeric($width)) {
        	$width .= 'px';
        }
        ?>

        <div id="yamap" style="width:<?=$width?>;height:<?=$height?>px;<?=($slow?'display:none;':'')?>"></div>
        <script type="text/javascript">

        function initMap()
        {
            if (document.getElementById('yamap') == null) {
                return ;
            }
            if (typeof(ymaps) == 'undefined' || typeof(ymaps.Map) == 'undefined') {
                var el = document.createElement('script');
                el.type = 'text/javascript';
                el.src = 'https://api-maps.yandex.ru/2.0-stable/?lang=ru-RU&load=package.standard,<?=$objects?'package.geoObjects,package.clusters,':''?>package.controls';
                headNode.appendChild(el);
                console.log('initMap timeout')
                setTimeout(initMap, 250);
                return ;
            }
            document.getElementById('yamap').style.display = 'block';
            initMapInsert();
        }
        function initMapInsert()
        {
            console.log('initMapInsert')
            ymaps.ready(function () {
                if (document.getElementById('yamap') == null) {
                    console.log('нет yamap выходим')
                    return ;
                }
                if ($('#yamap ymaps').length) {
                    console.log('уже нарисовано!')
                    return ;
                }
                console.log('imap рисуем!')
                imap = new ymaps.Map("yamap", {
                    center: [61.669587, 50.817189],
                    zoom: <?=$zoom?>,
                    behaviors: ["default", "scrollZoom"]
                });
                if (typeof(addYmapFullscreen) != 'undefined') {
                	addYmapFullscreen(imap)
                }
                <?php
                if ($callAfterLoad) {
                	echo $callAfterLoad.'();';
                }
                foreach ($tools as $k => $v) {
                    if ($v) {
                    	echo 'imap.controls.add(\''.$v.'\');';
                    }
                }
                echo "\n";
      
                if ($address) {
                ?>
                    var myGeocoder = ymaps.geocode("<?=addslashes($address)?>");
                    myGeocoder.then(
                        function (res)
                        {
                            if (typeof(imap) == 'undefined') {
                                return ;
                            }
                            imap.placemark_ref = res.geoObjects.get(0);
                            imap.geoObjects.add(res.geoObjects);
                            imap.setCenter(imap.placemark_ref.geometry.getCoordinates());
                        }
                    );<?php
                }
                if ($objects) {
                	Map::addMapObjects($objects);
                }
                ?>
            });
        }
        <?php if (!$slow) { ?>
        initMap()
        <?php } ?>


        </script>
        <?php
    }
    

    /**
     * Возвращает условный рисунок для данного типа и цены (используется в карте)
     */
    public function getImageByType($id_type, $cena, $default = true)
    {

        $color = "";
        $cena = $cena / 1000;
        if ($cena <= 1000) {
            $color = "";
        } elseif ($cena > 1000 && $cena <= 2000) {
            $color = 2;
        } elseif ($cena > 2000 && $cena <= 3000) {
            $color = 3;
        } elseif ($cena > 3000) {
            $color = 4;
        }

        if ($default) {
            $href = '/images/nedv/1-'.$color.'.gif';
            $size = array(21, 16);
        }

        switch ($id_type) {
            case 1:
                $href = '/images/nedv/hostel'.$color.'.gif';
                $size = array(21, 16);
                break;
            case 2: case 3: case 41: case 42: case 43: case 44:
                $href = '/images/nedv/apartment'.$color.'.gif';
                $size = array(20, 16);
                break;
            case 5:
                $href = '/images/nedv/garage'.$color.'.gif';
                $size = array(22, 16);
                break;
            case 4: case 6:
                $href = '/images/nedv/Home'.$color.'.gif';
                $size = array(18, 16);
                break;
            case 7:
                $href = '/images/nedv/areas'.$color.'.gif';
                $size = array(18, 16);
                break;
            case 8: case 9: case 10: case 11: case 12: case 14:
                $href = '/images/nedv/office'.$color.'.gif';
                $size = array(21, 16);
                break;
            case 13:
                $href = '/images/nedv/fasenda'.$color.'.gif';
                $size = array(26, 16);
                break;
            case 17:
                $href = '/images/nedv/cottage'.$color.'.gif';
                $size = array(18, 16);
                break;
            case 20:
                $href = '/images/nedv/townhose'.$color.'.gif';
                $size = array(21, 16);
                break;
            case 31:
                $href = '/images/nedv/1-'.$color.'.gif';
                $size = array(20, 16);
                break;
            case 32:
                $href = '/images/nedv/2-'.$color.'.gif';
                $size = array(20, 16);
                break;
            case 33:
                $href = '/images/nedv/3-'.$color.'.gif';
                $size = array(20, 16);
                break;
            case 34:
                $href = '/images/nedv/4-'.$color.'.gif';
                $size = array(20, 16);
                break;
        }

        return array($href, $size);
    }


    /**
     * Разместить объекты на карте - создается код к карте. Сама карта не создается.
     */
    public static function addMapObjects($objects)
    {
        if ($_COOKIE['dev'] && !Yii::app()->request->isAjaxRequest) {
            return ;
        }

        if (!$objects || !is_array($objects)) {
            return '';
        }
        $addTypes = Objects::getAddTypes();


        // Если это массивы из getAll, где итем находится в ключе item
        /*if (isset($objects[0]['item'])) {
            $objectsItems = array();
            foreach ($objects as $k => $v) {
                $objectsItems []= $v['item'];
            }
            $latlon = Houses::getLatLon($objectsItems);
        } else {
            $latlon = Houses::getLatLon($objects);
        }*/

        echo 'var myGeoObjects = [];';
        $added = 0;
        foreach ($objects as $item) {

            // Заполнение лат/лон
            if (!$item->lat) {
                ObjectUtils::addLatLon($item);
            }

            // echo '<br />'.$item->id.' ул.'.$item->street.' лат='.$item->lat; continue;

            $existCoords = ObjectUtils::printGeoObject($item);

            // Если у объекта есть координаты, то размещаем его прямо на карту
            if ($existCoords) {
                $added ++;
                echo 'imap.geoObjects.add(myGeoObject);';
            // Иначе размещаем в кластер
            } else {
                echo 'myGeoObjects.push(myGeoObject);';
            }
        }
        ?>
        if (myGeoObjects.length) {
            var myClusterer = new ymaps.Clusterer();
            myClusterer.add(myGeoObjects);
            imap.geoObjects.add(myClusterer);
        }
        <?php
        return $added;

    }

    /**
     * Проставить на карту указанный массив объявлений
     */
    public function printObjectsMap($objects)
    {
        if ($_COOKIE['dev']) {
            return ;
        }
        $latlon = Houses::getLatLon($objects);

        ?>
        <script src="https://api-maps.yandex.ru/2.0-stable/?lang=ru-RU&amp;load=package.standard,package.controls,<?php
        ?>package.geoObjects" type="text/javascript"></script>
        <div id="yamap" style="width:1000px; height:600px;"></div>
        <script type="text/javascript">
        var map;
        ymaps.ready(function(){
            var map = new ymaps.Map("yamap", {
                center: [61.669587, 50.837189],
                zoom: 13
            });
        <?php
        foreach ($objects as $v) {
            list($a, $b) = $latlon[$v->id];
        ?>
            myGeoObject = new ymaps.GeoObject({
                geometry: {
                    type: "Point",
                    coordinates: [<?=$a?>, <?=$b?>]
               },
                properties: {
                    hintContent: '<?=$v->getAddress()?>'
                }
            });
            map.geoObjects.add(myGeoObject);
        <?php
        }
        ?>

        })
        </script>
        <?php
    }


}

