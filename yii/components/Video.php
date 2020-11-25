<?php

class Video
{

    /**
     * Ссылка не предпросмотр ролика с первьюшкой
     */
    public function getPreviewLink($video, $title = '')
    {
        list($img, $embedUrl) = Video::getPreview($video, 0);
        if (!$img) {
            return false;
        }
        $w = 640; $h = 360;
        $w *= 2;  $h *= 2;
        return '<a title="'.$title.'" class="iframe" href="'.$embedUrl.'">'.$img.'</a>';
    }

    /**
     * На основе ссылки на видеоролик, возвращает превьюшки и ембед-урл
     *
     * list($image, $embedUrl) = Video::getPreview($video, 0);
     * list($image, $previewMini, $embed) = Video::getPreview($href, $img=2);
     */
    public function getPreview(&$url, $img = 1)
    {
        $url = str_replace('&amp;', '&', preg_replace('~\s+~', '', strip_tags($url)));
        if (empty($url)) {
            return false;
        }
        if (substr_count($url, '.') < 1) {
            echo 'Неверный урл "'.$url.'"';
            return false;
        }
        if (strpos($url, 'http') !== 0) {
        	$url = 'http://' . preg_replace('~^[.:/]+~i', '', $url);
        }
        if (strpos($url, 'youtube.com')) {
            if (preg_match_all('~(embed/|v=)([-a-z\d_]+)~i', $url, $reg)) {
                $videoId = $reg[2][0];
            } else {
                return false;
            }
            $embedUrl       = 'http://www.youtube.com/embed/'.$videoId;
            $httpUrl        = 'http://www.youtube.com/watch?v = '.$videoId;
            $previewUrl     = 'http://img.youtube.com/vi/'.$videoId.'/0.jpg';
            $previewMiniUrl = 'http://img.youtube.com/vi/'.$videoId.'/1.jpg';
        } else {
            $content = Video::getContentByUrl($url);
            if (strpos($url, 'mail.ru/videos/embed')) {
                $embedUrl = $url;
            } else {
                preg_match('~itemprop="embedURL" content="(.*?)"~i', $content, $reg);
                $embedUrl = $reg[1];
                if (!$embedUrl) {
                    preg_match('~embed src="(.*?)"~i', $content, $reg);
                    $embedUrl = $reg[1];
                    if (!$embedUrl) {
                        preg_match('~<meta property="og:video:iframe" content="(.*?)"~i', $content, $reg);
                        $embedUrl = $reg[1];
                        if (!$embedUrl) {
                            preg_match('~rel="video_src" href="(.*?)"~i', $content, $reg);
                            $embedUrl = $reg[1];
                            if (!$embedUrl) {
                                preg_match('~<meta name="twitter:player" content="(.*?)"~i', $content, $reg);
                                $embedUrl = $reg[1];
                            }
                        }
                    }
                }
            }

            preg_match('~<link rel="image_src" href="(.*?)" />~i', $content, $reg);
            $previewUrl = $reg[1];
            if (!$previewUrl) {
                preg_match('~<meta property="og:image" content="(.*?)"~i', $content, $reg);
                $previewUrl = $reg[1];
            }
        }

        if (!$embedUrl) {
            return false;
        }
        $imgTag = '';
        if ($previewUrl) {
            $imgTag = '<img style="width:250px;" src="'.$previewUrl.'" />';
        }
        if ($img) {
            if ($img == 2) {
                return array($previewUrl, $previewMiniUrl, $embedUrl);
            }
            return $imgTag;
        }
        return array($imgTag, $embedUrl);
    }

    /**
     * На основе ссылки на видеролик возвращает Титл и Описание ролика
     */
    function extractInfo(&$url)
    {
        $content = Video::getContentByUrl($url);
        if (!$content) {
            return array();
        }
        preg_match('~<meta property="og:title" content="(.*?)"~i', $content, $reg);
        $title = $reg[1];
        preg_match('~<meta property="og:description" content="(.*?)"~i', $content, $reg);
        $description = $reg[1];
        return array($title, $description);
    }

    /**
     * Загружает контент ролика на основе ссылки на ролик
     */
    function getContentByUrl(&$url)
    {
        // если это какой-то код, то пробуем разобрать
        if (strpos($url, '<') !== false ) {
            preg_match('~src="(.*?)"~i', $url, $reg);
            $url = $reg[1];
            if (!$url) {
                return '';
            }
        }
        $content = Utils::loadUrl($url, $cash = 0, $expired = 0, $fromCash, 1);
        $content = str_replace('&quot;', '"', $content);
        return $content;
    }
}

