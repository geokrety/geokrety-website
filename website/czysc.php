<?php

function longlinewrap($str, $maxLength = 45, $char = ' ')
{
    $wordEndChars = array(' ', "\n", "\r", "\f", "\v", "\0");
    $count = 0;
    $newStr = '';
    $openTag = false;
    for ($i = 0; $i < strlen($str); ++$i) {
        $newStr .= $str[$i];

        if ($str[$i] == '<') {
            $openTag = true;
            continue;
        }
        if (($openTag) && ($str[$i] == '>')) {
            $openTag = false;
            continue;
        }

        if (!$openTag) {
            if (!in_array($str[$i], $wordEndChars)) {
                //If not word ending char
                ++$count;
                if ($count == $maxLength) {
                    //if current word max length is reached
                    $newStr .= $char; //insert word break char
                    $count = 0;
                }
            } else {
                //Else char is word ending, reset word char count
                $count = 0;
            }
        }
    }//End for
    return $newStr;
}

function czysc($string)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    //return  (trim(mysqli_escape_string($link, htmlentities(strip_tags(stripslashes($newStr)), ENT_QUOTES, 'UTF-8'))));
    // iconv("UTF-8","UTF-8//IGNORE",$text);
    $string = (nl2br(trim(htmlentities(stripslashes(strip_tags(iconv('UTF-8', 'UTF-8//IGNORE', $string))), ENT_NOQUOTES, 'UTF-8'))));

    // przepisalem to
    // $string = longlinewrap(ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]"," [<a href=\"\\0\">Link</a>] ", $string));
    // w to, bo: ereg_replace function has been DEPRECATED as of PHP 5.3.0. Relying on this feature is highly discouraged.
    $string = longlinewrap(preg_replace("/(\s|^|[^a-z0-9])((?:https?|ftp):\/\/[^\"\'\s\]\<]+)/si", "$1[<a href='$2' rel=nofollow>Link</a>]", $string));

    return mysqli_real_escape_string($link, $string);
}

//troszke bardziej lightowa wersja (nie usuwa tagow) - jednak nie uzywana
// function czysc2($string){
// $link = DBConnect();
// $string =	nl2br(
                // trim(
                    // htmlentities(
                        // iconv("UTF-8","UTF-8//IGNORE", $string),
                        // ENT_NOQUOTES, "UTF-8"
                        // )
                    // )
                // );
// $string = preg_replace('/\s((?:https?|ftp):\/\/[^\"\'\s]+)(\s|$)/si', '[<a href="$1" rel=nofollow>Link</a>]', $string);
// return mysqli_real_escape_string($link, $string);
// }

function parse_bbcode($string)
{
    include 'templates/konfig.php';
    $link = DBConnect();

    $string = htmlentities(iconv('UTF-8', 'UTF-8//IGNORE', $string), ENT_NOQUOTES, 'UTF-8');

    // http://forums.codecharge.com/posts.php?post_id=77123
    //         "/\[code\](.*?)\[\/code\]/is" => "<pre style='border:1px solid #D7D7D7; padding:0.25em;' class='code'>$1</pre>",
    //      "/\[code\](.*?)\[\/code\]/is" => "<textarea style='width:98%;'>$1</textarea>",

    // zamiana [ na &#91; zeby uniknac zamiany bbcodu na html wewnatrz tagow [code] - taki szybki hack ;-)
    $string = preg_replace("/(\[code\].*?)(\[)(.*?\[\/code\])/is", '$1&#91;$3', $string);

    $bbcode = array(
        "/\[code\](.*?)\[\/code\]/is" => "<pre style='border:1px solid #bfd0d9; padding:0.25em;'>$1</pre>",
        "/\[b\](.*?)\[\/b\]/is" => '<b>$1</b>',
        "/\[i\](.*?)\[\/i\]/is" => '<i>$1</i>',
        "/\[u\](.*?)\[\/u\]/is" => '<u>$1</u>',
        "/\[url\=(.*?)\](.*?)\[\/url\]/is" => "<a href='$1'>$2</a>",
        "/\[url\](.*?)\[\/url\]/is" => "<a href='$1'>$2</a>",
        "/\[img\](.*?)\[\/img\]/is" => "<img src='$1' alt='' />",
        "/(?:\s|^)((?:https?|ftp):\/\/[^\"\'\s\]]+)(?:\s|$)/si" => "[<a href='$1' rel=nofollow>Link</a>]",
    );

    $string = preg_replace(array_keys($bbcode), array_values($bbcode), $string);

    $string = nl2br(trim($string));

    //usuwanie dodanych br z tresci pomiedzy tagami  <code>
    while ($string_old != $string) {
        $string_old = $string;
        $string = preg_replace("/(<pre style=.*?)(<br \/>)(.*?<\/pre>)/is", '$1$3', $string);
    }

    return mysqli_real_escape_string($link, $string);
}
