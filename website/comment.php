<?php

require_once '__sentry.php';

require_once 'wybierz_jezyk.php'; // choose the user's language
require 'templates/konfig.php';

// smarty cache
$smarty_cache_this_page = 0; // this page should be cached for n seconds
require_once 'smarty_start.php';

require_once 'longin_chceck.php';
$longin_status = longin_chceck();
$userid = $longin_status['userid'];

function APIerror()
{
    // returns error in xml
    global $longin_status;
    if ($longin_status['mobile_mode'] == 1) {
        $errors[] = _('Unknown error.');
        if (!defined(a2xml)) {
            include_once 'fn_a2xml.php';
        }
        echo a2xml($errors, 'errors', 'error');
        exit;
    } // mobile version
}

function return_error_message($message)
{
    if ($longin_status['mobile_mode'] == 1) {
        $errors[] = $message;
        if (!defined(a2xml)) {
            include_once 'fn_a2xml.php';
        }
        echo a2xml($errors, 'errors', 'error');
        exit;
    } // mobile version

    else {
        $TRESC = "<div style='background:#ececec; width:400px; padding:20px 20px;'>
		<div style='padding-bottom:0.5em'>$message</div>";

        return $TRESC;
    }
}
$something_went_wrong = _('Oops! Something went wrong.');

$p_comment = $_POST['comment'];
// autopoprawione...
$p_comment_esc = $_POST['comment_esc'];
// autopoprawione...
$p_gk_id = $_POST['gk_id'];
// autopoprawione...
$p_ruch_id = $_POST['ruch_id'];
// autopoprawione...
$p_type = $_POST['type'];
// autopoprawione...import_request_variables('p', 'p_');

$g_confirmed = $_GET['confirmed'];
// autopoprawione...
$g_delete = $_GET['delete'];
// autopoprawione...
$g_gkid = $_GET['gkid'];
// autopoprawione...
$g_gk_id = $_GET['gk_id'];
// autopoprawione...
$g_ruchid = $_GET['ruchid'];
// autopoprawione...
$g_type = $_GET['type'];
// autopoprawione...import_request_variables('g', 'g_');

// niezalogowani out?
if ($longin_status['plain'] == null) {
    if (ctype_digit($g_gkid) and ctype_digit($g_ruchid)) {
        $TRESC = return_error_message("<strong><a href='/longin.php' onclick='$.fn.colorbox.close(); setTimeout(function(){parent.location.href=\"/longin.php\";},400); return false;'>"._('Please login.').'</a></strong>');
        echo $TRESC;
        exit;
    } else {
        setcookie('longin_fwd', base64_encode($_SERVER['REQUEST_URI']), time() + 120);
        header('Location: /longin.php');
        // include_once('defektoskop.php');
        // $TRESC = defektoskop("<strong><a href='/longin.php'>". _('Please login.') ."</a></strong>");
        // include_once('smarty.php');
        exit;
    }
}

if (isset($p_comment)) {
    $p_comment = trim($p_comment);
}

$link = DBConnect();

require_once 'db.php';
$db = new db();

require_once 'defektoskop.php';

// FORMULARZ
if (ctype_digit($g_gkid) and ctype_digit($g_ruchid)) {
    $missing_report = (($g_type == 'missing') /* && (count($_GET)==3) */);

    //sprawdzenie czy mozemy uzyc tych danych do zgloszenia zaginiecia
    //a jezeli dodajemy normalny komentarz to tez trzeba sprawdzic dane wejsciowe i pobrac nazwe kreta itp
    if ($missing_report) {
        $sql = "SELECT gk.nazwa, ru.koment, ru.komentarze
		FROM `gk-ruchy` ru
		LEFT JOIN `gk-geokrety` gk ON ( ru.id = gk.id )
		WHERE ru.ruch_id='$g_ruchid' AND ru.id='$g_gkid' AND ru.ruch_id=gk.ost_pozycja_id AND ru.logtype IN ('0','3') AND gk.typ!='2'
		LIMIT 1";

        $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Przy tym logu nie mozna zglosic zaginiecia.'.' [#'.__LINE__.']', 7);

        // jak wykryto blad to nie ma przebacz, bye!

        if ($num_rows <= 0) {
            APIerror();
            echo return_error_message($something_went_wrong.' [#'.__LINE__.']');
            exit;
        }
        list($geokret_name, $ruchy_text, $komentarze) = $row;

        $title_text = sprintf(_('Reporting GeoKret %s as missing'), $geokret_name);
        $background = '#ececec';
        $separator_color = '#ff7777';
    } else { //jezeli zwykly komentarz
        //najpierw pobieramy nazwe kreta itp sprawdzajac tez czy pasuja dane ruch_id i gk_id
        $sql = "SELECT gk.nazwa, ru.koment, ru.komentarze, us.user, ru.username, ru.data_dodania
		FROM `gk-ruchy` ru
		LEFT JOIN `gk-geokrety` gk ON ( ru.id = gk.id )
		LEFT JOIN `gk-users` us ON ( ru.user = us.userid )
		WHERE ru.ruch_id=$g_ruchid AND ru.id=$g_gkid
		LIMIT 1";

        $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Dla tych danych wejsciowych (gk_id i ruch_id) nie mozna dodac komentarza!'.' [#'.__LINE__.']', 7);

        // jak wykryto blad to nie ma przebacz, bye!
        if ($num_rows <= 0) {
            APIerror();
            echo return_error_message($something_went_wrong.' [#'.__LINE__.']');
            exit;
        }

        list($geokret_name, $ruchy_text, $komentarze, $user, $username, $date) = $row;
        $username = $username ?: $user;

        $title_text = sprintf(_('Commenting GeoKret: %s'), $geokret_name);
        $background = '#ececec';
        $separator_color = 'silver';
    }

    $TRESC = '
  <div class="modal-header'.($missing_report ? ' alert-danger' : '').'">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="infoModalLabel">'.$title_text.'</h4>
  </div>
  <form name="comment" action="'.$_SERVER['PHP_SELF'].'" method="post" >
    <div class="modal-body">';

    if ($missing_report) {
        $TRESC .= '<p>'._('Please provide any information that may help the owner to find out the whereabouts of this GeoKret.').'</p><hr>';
    } else {
        if (!empty($ruchy_text)) {
            $TRESC .= '<h5>'._('Log:').'</h5><blockquote><p>'.$ruchy_text.'<footer>'.sprintf(_('By %s, on %s'), $username, $date).'</footer></p></blockquote><hr>';
        }
    }

    //jezeli do ruchu jest juz jakis komentarz to go wyswietlimy (zawsze tylko ostatni)
    if (!$missing_report && $komentarze > 0) {
        $sql = "SELECT co.user_id, co.comment, us.user, co.type, co.data_dodania
		FROM `gk-ruchy-comments` co
		LEFT JOIN `gk-users` us ON ( co.user_id = us.userid )
		WHERE co.kret_id=$g_gkid AND co.ruch_id=$g_ruchid
		ORDER BY co.comment_id DESC LIMIT 1";
        $row = $db->exec_fetch_row($sql, $num_rows, 1);
        if ($num_rows == 1) {
            list($last_userid, $last_comment, $last_username, $last_type, $last_date) = $row;
            if (!empty($last_comment)) {
                $TRESC .= '<h5>'._('Last comment:').'</h5><blockquote class="'.($last_type ? 'bg-danger' : '').'"><p>'.$last_comment.'<footer>'.sprintf(_('By %s, on %s'), $last_username, $last_date).'</footer></p></blockquote><hr>';
            }
        }
    }

    if ($missing_report) {
        $missing_hidden_input = "<input type='hidden' name='type' value='missing' />";
    }

    $TRESC .= '
    <div class="form-group">
      <label class="control-label">'._('Your comment').'</label>
      <input type="text" class="form-control" name="comment" id="text_field" maxlength="500" autofocus>
    </div>
    <input type="hidden" name="gk_id" value="'.$g_gkid.'">
    <input type="hidden" name="ruch_id" value="'.$g_ruchid.'">
    '.$missing_hidden_input.'
  </div>
  <div class="modal-footer">
    <button type="submit" class="btn btn-primary">'._('Send message').'</button>
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  </div>
  </form>';

    $TRESC .= "<script type=\"text/javascript\">
    $(function () {
      $('#text_field[maxlength]').maxlength({threshold: 100});
    })
    </script>\n";

    // $TRESC = "";

    echo $TRESC;
    exit;
}

// DODAWANIE KOMENTARZY
else {
    if ((!empty($p_comment)) and ctype_digit($p_gk_id) and ctype_digit($p_ruch_id)) {
        include_once 'aktualizuj.php';
        $missing_report = ($p_type == 'missing');

        if ($missing_report) {
            //sprawdzamy czy mamy dobre dane wejsciowe
            $sql = "SELECT ru.ruch_id FROM `gk-ruchy` ru
		LEFT JOIN `gk-geokrety` gk ON (ru.id=gk.id)
		WHERE ru.ruch_id='$p_ruch_id' AND ru.id='$p_gk_id' AND gk.typ!='2'";
        } else {
            //sprawdzamy czy mamy dobre dane wejsciowe
            $sql = "SELECT ru.ruch_id FROM `gk-ruchy` ru
		WHERE ru.ruch_id='$p_ruch_id' AND ru.id='$p_gk_id'";
        }
        $db->exec_num_rows($sql, $num_rows, 0, 'Dla tych danych wejsciowych (post) nie mozna dodac komentarza.'.' [#'.__LINE__.']', 7);

        // jak wykryto blad to nie ma przebacz, bye!
        if ($num_rows <= 0) {
            APIerror();
            include_once 'defektoskop.php';
            $TRESC = defektoskop($something_went_wrong.' [#'.__LINE__.']', false);
            include_once 'smarty.php';
            exit;
        }

        //$p_comment_esc = mysqli_real_escape_string($link, $p_comment);
        include_once 'czysc.php';
        $p_comment_esc = czysc($p_comment);

        /*
        if ($missing_report)
        {
            // data z koncowka sekund 06 aby w tabeli ruchy nie kolidowalo z jakims istniejacym ruchem (tam wszystkie maja koncowki 00)
            // nie wiem co by bylo gdyby byly dwa logi o identycznych sekundach ale pewnie filips wie :P
            $now=date("Y-m-d H:i:06");
            //wpisujemy niewidzialny logtype 6 tak aby zmniejszyc ilosc zmian w module exportujacym i zeby OC wiedzialo kiedy wyjac kreta ze skrzynki
            $sql = "INSERT INTO `gk-ruchy` (`id`, `data`, `user`, `koment`, `logtype`, `username`, `data_dodania`)
                    VALUES ('$p_gk_id', '$now', '$userid', '$p_comment_esc', '6', '', '$now')";
            $result = mysqli_query($link, $sql);

            if((!$result) OR (mysqli_affected_rows($result)==0))
            {
                include_once('defektoskop.php');
                if(!$result) errory_add("SQL FAILED: $sql",100);
                else if(mysqli_num_rows($result)==0) errory_add("SQL RETURN 0: $sql",100);
                header("Location: konkret.php?id=$p_gk_id");
                exit;
            }

            // jak juz wpisalismy to pobieramy id tego ruchu aby wpisac go w polu extra tabeli komentarzy
            $sql = "SELECT `ruch_id`
                    FROM `gk-ruchy`
                    WHERE ((`id`='$p_gk_id') AND (`logtype`='6') AND (`user`='$userid') AND (`data_dodania`='$now') AND (`data`='$now'))
                    LIMIT 1";
            $result = mysqli_query($link, $sql);
            if((!$result) OR (mysqli_num_rows($result)==0))
            {
                include_once('defektoskop.php');
                if(!$result) errory_add("SQL FAILED: $sql",100);
                else if(mysqli_num_rows($result)==0) errory_add("SQL RETURN 0: $sql",100);
                header("Location: konkret.php?id=$p_gk_id");
                exit;
            }
            $row = mysqli_fetch_array($result);
            if($result) mysqli_free_result($result);
            $ruch_id_logtype6 = $row[0];

            $comment_type='1';
            $comment_extra=$ruch_id_logtype6;
        }
        else
        {
            $now=date("Y-m-d H:i:s");
            $comment_type='0';
            $comment_extra='0';
        }
        */

        $now = date('Y-m-d H:i:s');
        if ($missing_report) {
            $comment_type = '1';
        } else {
            $comment_type = '0';
        }

        $sql = "INSERT INTO `gk-ruchy-comments` (`ruch_id`, `kret_id`, `user_id`, `data_dodania`, `comment`, `type`)
		  VALUES ('$p_ruch_id', '$p_gk_id', '$userid', '$now', '$p_comment_esc', '$comment_type')";
        $db->exec_num_rows($sql, $num_rows, 0, 'Blad podczas dodawania nowego rekordu-komentarza.'.' [#'.__LINE__.']', 7);

        aktualizuj_komentarze_dla_ruchu($p_ruch_id);

        //jesli kasujemy komentarz zaginiecia to musimy tez updatnac pole missing  w tabeli geokretow
        if ($missing_report) {
            aktualizuj_missing_dla_kreta($p_gk_id);
        }

        //BIG BROTHER (chwilowy)
        include_once 'defektoskop.php';
        if ($missing_report) {
            errory_add('NEW MISSING COMMENT', 0);
        } else {
            errory_add('NEW COMMENT', 0);
        }

        if ($longin_status['mobile_mode'] == 1) { // logging via api
            // xml with no errors
            $now = date('Y-m-d H:i:s');
            echo '<?xml version="1.0"?>'."\n<gkxml version=\"1.0\" date=\"$now\">";
            echo "<errors><error></error></errors>\n";
            echo "<geokrety><geokret id=\"$kretid\" /></geokrety>";
            echo '</gkxml>';

            return $return;
            exit();
        } else {
            header("Location: konkret.php?id=$p_gk_id#log$p_ruch_id");
            exit;
        }
    }

    //USUWANIE KOMENTARZY
    else {
        if (ctype_digit($g_delete) and ($g_confirmed == '1')) {
            include_once 'aktualizuj.php';

            //najpierw pobieramy interesujace nas dane sprawdzajac tez czy dane wejsciowe pasuja do siebie i czy dany user moze usunac komentarz
            $sql = "SELECT co.ruch_id, co.kret_id, co.type, co.user_id, gk.owner
			FROM `gk-ruchy-comments` co
			LEFT JOIN `gk-geokrety` gk ON (co.kret_id = gk.id)
			WHERE co.comment_id='$g_delete' LIMIT 1";
            $row = $db->exec_fetch_row($sql, $num_rows, 0, 'Nieudana proba usuniecia komentarza, brak komentarza!'.' [#'.__LINE__.']', 7);

            // jak wykryto blad to nie ma przebacz, bye!
            if ($num_rows <= 0) {
                APIerror();
                $smarty_cache_this_page = 0;
                include_once 'smarty_start.php';
                include_once 'defektoskop.php';
                $TRESC = defektoskop('Comment not found!'.' [#'.__LINE__.']', false);
                include_once 'smarty.php';
                exit;
            }

            list($ruch_id, $gk_id, $type, $autor_postu, $gk_owner) = $row;

            // sprawdzamy czy dany user ma prawo skasowac ten komentarz
            if (($userid != $autor_postu) && ($userid != $gk_owner)) {
                $smarty_cache_this_page = 0;
                include_once 'smarty_start.php';
                include_once 'defektoskop.php';
                $TRESC = defektoskop('Cannot remove this comment!'.' [#'.__LINE__.']', false);
                include_once 'smarty.php';
                exit;
            }

            $missing_report = ($type == '1');

            /*
            //jesli kasujemy komentarz zaginiecia to musimy tez usunac niewidoczny log zaginieica
            if ($type='1' && ctype_digit($extra))
            {
                $sql="DELETE FROM `gk-ruchy` WHERE `ruch_id` = '$extra' LIMIT 1";
                $result = mysqli_query($link, $sql);
                if(!$result) {include_once('defektoskop.php');errory_add("SQL FAILED: $sql",100);}
                else if(mysqli_affected_rows($link)==0) {include_once('defektoskop.php');errory_add("SQL AFFECTED 0: $sql",100);}
                if($result) mysqli_free_result($result);
            }
            */

            $sql = "DELETE FROM `gk-ruchy-comments` WHERE `comment_id` = '$g_delete' LIMIT 1";
            $db->exec($sql, $num_rows, 0, 'Blad podczas usuwania rekordu-komentarza.', 7);

            aktualizuj_komentarze_dla_ruchu($ruch_id);

            //jesli kasujemy komentarz zaginiecia to musimy tez updatnac pole missing w tabeli geokretow
            if ($missing_report) {
                aktualizuj_missing_dla_kreta($gk_id);
            }

            //BIG BROTHER (chwilowy)
            include_once 'defektoskop.php';
            errory_add('DELETE COMMENT', 0);

            header("Location: konkret.php?id=$gk_id#log$ruch_id");
            exit;
        }

        // OBSLUGA PUSTEGO FORMULARZA
        else {
            if ((empty($p_comment)) and (ctype_digit($p_gk_id)) and (ctype_digit($p_ruch_id))) {
                APIerror();
                include_once 'defektoskop.php';
                errory_add('Obsluga komentarzy - brak komentarza', 0);
                header("Location: konkret.php?id=$p_gk_id#log$p_ruch_id");
                exit;
            }

            // INNE DZIWNE ZDARZENIA.
            // JEZELI JEST KTORYS GK_ID TO PRZEKIEROWANIE Z POWROTEM NA STRONE KRETA
            else {
                if (ctype_digit($p_gk_id)) {
                    APIerror();
                    include_once 'defektoskop.php';
                    errory_add('Obsluga komentarzy - brak wszystkich danych?', 50);
                    header("Location: konkret.php?id=$p_gk_id");
                    exit;
                }

                // HMM...
                else {
                    APIerror();
                    include_once 'defektoskop.php';
                    errory_add('<b>eee? czyzby zly url a moze ktos sie wlamuje?</b>', 100);
                    echo return_error_message($something_went_wrong.' [#'.__LINE__.']');
                    exit;
                }
            }
        }
    }
}
