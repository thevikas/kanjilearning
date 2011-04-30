<?php

#todo, many times same work is shown twice
require_once("db.php");
global $debugprinting;
$debugprinting=1;

mb_language('uni');
mb_internal_encoding('UTF-8');

print_r($_REQUEST);

if(isset($_REQUEST['chapter']))
{
    $ids = $_REQUEST['ids'];

    foreach($_REQUEST as $k=>$v)
    {
        $qmeans = $v;
        if(strstr($k,"word_") && strlen($qmeans)>2)
        {
            $ss = explode("_",$k);
            $qword = $ss[1];
            if(isset($ss[2]))
            {
                $ids[] =  clsWord::addnew($qword,$qmeans,$ss[2]);
            }
            else
                $ids[] =  clsWord::addnew($qword,$qmeans);
        }
    }
    clsWord::changechapter(implode(",",$ids),$_REQUEST['chapter']);
}
elseif(isset($_GET['words']))
{
    ?>
    <form method="POST">
    <ol><?
    $debugprinting=1;
    $qwords = html_entity_decode($_GET['words'], ENT_NOQUOTES, 'UTF-8');
    $qwords = str_replace(",", "、", $qwords);
    $qwords = str_replace("\n", "、", $qwords);
    $qwords = str_replace("（","(", $qwords);
    $qwords = str_replace("）",")", $qwords);
    $qwords = str_replace("　"," ", $qwords);
    $qwords = trim($qwords);
    $ss = explode("、",$qwords);
    $ids = array();
    foreach($ss as $v0)
    {
        if(strstr($v0, "(") !== false)
        {
            $ss1 = explode("(", $v0);
            $v = trim($ss1[0]);
            
            $ss2 = explode(")", $ss1[1]);
            $kanji_word = trim($ss2[0]);
        }
        else
            $v = $v0;

        $v2 = html_entity_decode($v, ENT_NOQUOTES, 'UTF-8');
        $w = clsWord::search($v2);
        if(isset($w) && $w->id)
        {
            $ids[] = $w->id;
            print <<<E
                <li><input checked=checked type="checkbox" name="ids[]"
                value="{$w->id}"/><b>{$w->word}</b> - <i>{$w->means}</i> - C<i>{$w->chapter}</i></li>
E;
        }
        else
        {
            ?>
                <li><b><?=$v?></b>:<input name="word_<?=$v?>_<?=$kanji_word?>" type="text" value=""/></li>
            <?
        }
    }
    ?></ol>

    <input name="ids2" value="<?=implode(',',$ids)?>"/>
    <input name="chapter" value=""/>
    <input type="submit"/>
    </form>
    <?
}
//わたし、わたしたち、あなた、あのひと、あのかた
?>

<h2>Enter more words to mark below.</h2>
<form>
<textarea name="words" rows="10" style="font-size: 1.5em" cols="80"/>
</textarea>
<br/>
<input type="submit"/>
</form>
