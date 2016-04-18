<?php

namespace AppBundle\Media;

/**
 * Helper class for video embedding
 * Original source code: https://sourceforge.net/p/kawf/git/ci/95c5adb1788da088099b04b0746045286582c853/tree/user/embed-media.inc.php
 * Reformatted for PSR
 *
 * TODO: REBUILD
 */
class VideoEmbedder
{
    /**
     * @var array
     */
    private $video_embedders = array('redtube', 'vimeo', 'youtube', 'html5', 'vine', 'gfy');

    /**
     * @param $query
     * @return mixed
     */
    public function explodeQuery($query)
    {
        $queryParts = explode('&', $query);

        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    /**
     * @param $url
     * @return string
     */
    public function embedRedtubeVideo($url)
    {
        if (preg_match("#^http://(\w+\.)*redtube\.com/([0-9]+)#", $url, $regs)) {
            $tag = $regs[2];
        } else {
            return null;
        }

        $out =
            "<object width=\"100%\" height=\"360\">\n" .
            "<param name=\"movie\" value=\"http://embed.redtube.com/player/></param>\n" .
            "<param name=\"FlashVars\" value=\"id=$tag\"></param>\n" .
            "<embed src=\"http://embed.redtube.com/player/?id=$tag\"" .
            " type=\"application/x-shockwave-flash\" width=\"100%\" height=\"360\"></embed>\n" .
            "</object><br>\n";

        return $this->tagMedia($out, "RedTube ", $url, $tag, "redtube");
    }

    /**
     * @param $url
     * @return string
     */
    public function embedVimeoVideo($url)
    {
        if (preg_match("#^http://(\w+\.)*vimeo\.com/([0-9]+)#", $url, $regs)) {
            $tag = $regs[2];
        } else {
            return null;
        }

        $out = "<iframe src=\"https://player.vimeo.com/video/$tag\"\n" .
            "\twidth=\"100%\" height=\"360\" frameborder=\"0\"\n" .
            "\twebkitAllowFullScreen mozallowfullscreen allowFullScreen>\n" .
            "</iframe><br>\n";

        return $this->tagMedia($out, "Vimeo ", $url, $tag, "vimeo");
    }

    /**
     * @param $url
     * @return string
     */
    public function embedYoutubeVideo($url)
    {
        $tag = null;

        $u = parse_url(html_entity_decode($url));
        if ($u == null || !isset($u['host'])) {
            return null;
        }

        if (preg_match("#(\w+\.)*youtube\.com#", $u["host"])) {
            $q = $this->explodeQuery($u["query"]);
            $p = explode("/", $u["path"]);
            if (array_key_exists('v', $q)) {
                $tag = $q["v"]; # http://youtube.com/?v=tag
            } else if (count($p) == 3 && ($p[1] == "v" || $p[1] == "embed")) {
                $tag = $p[2]; # http://youtube.com/(v|embed)/tag
            }
        } else if (preg_match("#(\w+\.)*youtu\.be#", $u["host"])) {
            $p = explode("/", $u["path"]);
            if (count($p) == 2) {
                $tag = $p[1]; # http://youtu.be/tag
            }
        }

        if ($tag == null) {
            return null;
        }

        $url = "https://youtube.googleapis.com/v/$tag?version=3&fs=1";
        $width = "100%";
        $height = 480;
        $out =
            "<object width=\"{$width}\" height=\"{$height}\">
            <param name=\"movie\" value=\"http://www.youtube.com/embed/{$tag}?html5=1&amp;rel=0&amp;hl=en_US&amp;version=3\"/>
            <param name=\"allowFullScreen\" value=\"true\"/>
            <param name=\"allowscriptaccess\" value=\"always\"/>
            <embed width=\"{$width}\" height=\"{$height}\" src=\"http://www.youtube.com/embed/{$tag}?html5=1&amp;rel=0&amp;hl=en_US&amp;version=3\" class=\"youtube-player\" type=\"text/html\" allowscriptaccess=\"always\" allowfullscreen=\"true\"/>
            </object>";
        return $this->tagMedia($out, "YouTube ", "http://youtu.be/$tag", $tag, "youtube");
    }

    /**
     * @param $url
     * @return string
     */
    public function getVineVideoFromUrl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        preg_match('/twitter:player:stream.*content="(.*)"/', $res, $output);
        return $output[1];
    }

    /**
     * @param $url
     * @return string
     */
    public function embedVineVideo($url)
    {
        $u = parse_url(html_entity_decode($url));
        if ($u == null || !isset($u['host'])) {
            return null;
        }

        if (preg_match("#(\w+\.)*vine\.co#", $u["host"])) {
            $p = explode("/", $u["path"]);
            if (count($p) == 3 && $p[1] == "v") {
                $tag = $p[2]; # http://vine.co/v/tag
            }
        } else {
            return null;
        }

        $src = getVineVideoFromUrl("http://vine.co/v/$tag");

        $out =
            "<video src=\"$src\" controls=\"controls\">\n" .
            "Your browser <a href=\"http://en.wikipedia.org/wiki/HTML5_video#Browser_support\">does not support HTML5 and/or this codec</a>.\n" .
            "</video><br>\n";

        return $this->tagMedia($out, "Vine ", "https://vine.co/v/$tag", $tag, "vine");

    }

    /**
     * @param $url
     * @return string
     */
    public function embedHtml5Video($url)
    {
        $u = parse_url(html_entity_decode($url));
        if ($u == null || !isset($u['host']) || !isset($u['path'])) {
            return null;
        }

        # only support ogg, mp4, and webm
        if (!preg_match("/\.(og[gvm]|mp[4v]|webm)$/i", $u["path"])) {
            return null;
        }

        $out =
            "<video src=\"$url\" controls=\"controls\" style=\"width:100%\">\n" .
            "Your browser <a href=\"http://en.wikipedia.org/wiki/HTML5_video#Browser_support\">does not support HTML5 and/or this codec</a>.\n" .
            "</video><br>\n";

        return $this->tagMedia($out, "", $url, "HTML5", "html5");
    }

    public function embedGfyVideo($url)
    {
        $u = parse_url(html_entity_decode($url));
        if ($u == null || !isset($u['host'])) {
            return null;
        }

        if ($u['host'] == "gfycat.com") {
            $path = substr($u['path'], 1);

            $out = "<img class=\"gfyitem\" data-id=\"{$path}\" data-title=true data-autoplay=false data-controls=true data-expand=false />";

            return $this->tagMedia($out, "", $url, $url, "gfy");
        }

        return null;
    }

    /**
     * @param $out
     * @param $prefix
     * @param $url
     * @param $text
     * @param $class
     * @param $redirect
     * @return string
     */
    public function tagMedia($out, $prefix, $url, $text, $class, $redirect = false)
    {
        // if ($redirect) {
        //     $out .= "$prefix<a href=\"/redirect.phtml?refresh&amp;url=" . urlencode($url) . "\" target=\"_blank\">$text</a>";
        // } else {
        //     $out .= "$prefix<a href=\"$url\" target=\"_blank\">$text</a>";
        // }

        // return "<div class=\"$class\">\n$out<br>\n</div>";
        return $out;
    }

    /**
     * @param $url
     * @return string
     */
    public function embedVideo($url)
    {
        foreach ($this->video_embedders as $embedder) {
            $f = "embed" . ucfirst($embedder) . "Video";
            $out = $this->$f($url);
            if (!is_null($out)) {
                return "<div class=\"embeddedVideo\">$out</div>";
            }
        }
        return null;
        // return "'$url' is not a supported video type. Must be YouTube/Vimeo link or ogg/mp4/WebM<p>\n";
    }

    /**
     * @param $url
     * @return string
     */
    public function embedImage($url)
    {
        $out = "<img src=\"$url\" alt=\"$url\">\n";
        return $this->tagMedia("", "", $url, $out, "imageurl", true/* hide referer */);
    }
}
