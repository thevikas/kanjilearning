<?php

/*
 * SELECT difference, count( * )
FROM `stats`
WHERE date( now( ) ) = date( dated )
GROUP BY difference
WITH rollup
LIMIT 0 , 30
 */


/*
 * SELECT chapter
,count(v.id) as `total-words`
,count(s.kanji_id) as `seen`
,100*(count(v.id) - count(s.kanji_id))/count(v.id) as `%-left-to-see`

FROM `vocab` v
left join stats s on s.kanji_id=v.id
where chapter between 1 and 10
group by chapter
with rollup;
#todo, many times same work is shown twice
 */
require_once("db.php");
global $debugprinting;
$debugprinting=1;

$means = "";
if(isset($_GET['means']))
    $means =$_GET['means'];
    
global $todaystring;
global $question;
$question= "";
$cls = "";

$todaystring = date("Y-m-d",time());

//if quiz mode if daily and there are no more words left
//we can change mode to non-daily if the user has time
$stats = clsWord::getStatistics();
if( !($stats['newleft']>0) && $quiz_mode==1)
{
    //debugprint("no more for today, change quiz mode");
    //$quiz_mode = 2;
    //$stats = clsWord::getStatistics();
}

$oldid = 0;
if(isset($_REQUEST['oldid']))
    $oldid = $_REQUEST['oldid'];


$word = clsWord::getNextWord($stats['newleft']>0,$oldid);
echo "<!-- currect word id: {$word->id} -->";
#$word = new clsWord();
#$word->load(328);

if(!$word)
    die("Nothing to do today");

#load lowest id kanji
#$r = $myi->query("select * from vocab where id not in (select kanji_id from score) limit 0,1");
#$rs = $r->fetch_array();
if($debugprinting)
echo "<!-- ";
    print_r($word);
echo "-->";

#QT defines the type of question
# 1 - japanese word with english options
# 2 - japanese word with english textbox
# 3 - english word with japanese options
# 4 - english word with japanese textbox
# 5 - japanese audio with english textbox


# ? - japanese word with kanji option
# ? - kanji word with japanese option
# ? - kanji word with english word

$qt=1;

$caption = "Meaning";

if($word->correct>5 && ($word->grade=='A' || $word->grade=='B'))
{
    debugprint("QT2");
    $qt++; // 2
}

#three cases
#1. Word is easy and its stright win most times; grade is A or B and times>10
#2. the word is hard, took time to learn; grade A or B and times> 25


#TODO: buggy, shows even when correct are less then right
#just shown counter is not enough for this mode.
if(
            ($word->shown>10 && ($word->grade=='A' || $word->grade=='B')) ||
            ($word->shown>25 && ($word->grade=='A' || $word->grade=='B'))
   )
{
    debugprint("QT3");
    $qt++; // 3
}
#TODO: buggy, shows even when correct are less then right
#just shown counter is not enough for this mode.
if(
            ($word->shown>15 && ($word->grade=='A' || $word->grade=='B')) ||
            ($word->shown>30 && ($word->grade=='A' || $word->grade=='B'))
   )
{
    debugprint("QT4");
    $qt++;// 4
}

if(
            ($word->shown>20 && ($word->grade=='A' || $word->grade=='B')) ||
            ($word->shown>35 && ($word->grade=='A' || $word->grade=='B'))
   )
{
    debugprint("QT5");
    $qt++; // 5
}

echo "<!-- corrent word QT: {$qt} -->";

//debugprint("switch($qt)");
switch($qt)
{
    case 1: //with hinting
        {
            $caption = "Choose in english";
            $means = clsWord::getRandomWords($word);
            $question = $word->word . " (<span class=\"kanji\">{$word->kanji}</span>)";;
            echo "<!-- case1 -->";
            break;
        }
    case 2: //no hinting
        {
            //nothing
            $caption = "Write in english";
            echo "<!-- case2 -->";
            $question = $word->word . " ({$word->kanji})";
            break;
        }
    case 3: 
        {
            //english word with japanese meanings
            $caption = "Choose in japanese";
            $means = clsWord::getRandomWords($word,0);
            echo "<!-- case3 -->";
            $question = $word->means;
            break;
        }
    case 4:
    case 5:
        {
            //nothing
            $caption = "Write in japanese";
            echo "<!-- case4 -->";
            $question = $word->means;
            break;
        }
    /*/*case 4: //no options, a textbox to answer in english
        {
            #TODO: need QT 4
        }
    case 5: //no options, a textbox to answer in english
    {
        #TODO: need QT 5
    }*/

}

require_once("header.php");
require_once("stats.php");
?>

<form method="post" action="check.php">
<input type="hidden" name="id" value="<?=$word->id?>"/>
<p class="w">
<label>Word</label>
<strong <?=$cls?>>
<?
echo $question
?>
</strong>
<!-- <sub><small><?=$word->difference?>,<?=$word->wrong?></small></sub> -->
</p>
<p class="m">
<label for="focusme">
<?=$caption?>
</label>
<?
if(0)
{
    ?>
    <strong>
    <label for="ml">
    <?=$rs['means']?>
    </label>
    </strong>
    <?
}
?></p>
<!--
MEANS:
<?
print_r($means);
?>
-->

<?php
if($qt==1 || $qt==3)
{
    $ri = rand(1,5);
    for($i=0; $i<5; $i++)
    {
        ?>
        <!-- $ri=<?=$ri?>; <?=$qt?> -->
        <input type="radio" id="r<?=$i?>" name="ans" value="<?=$means[$ri % 5]?>"/>
        <label for="r<?=$i?>"><?=$i?>: <?=$means[$ri % 5]?></label><br/>
        <?    
        $ri++;
    }
    ?>
    <input type="text" value="" autocomplete="off" id="focusme" onkeypress="return kp(event,this)"/>
    <?
}
?>
<input type="hidden" name="qt" value="<?=$qt?>"/>
<? if($qt==2 || $qt >= 4) { ?><input class="qt<?=$qt?>" autocomplete="off" id="focusme" type="text" name="meanslike"/> <? } ?>
<p>
<input type="submit"/>
</p>
</form>
<script type="text/javascript">
<? if($qt==1) { ?>
function kp(e,o)
{
    frm = o.form;
    if(e.keyCode==13)
    {
        if(o.value == '?')
        {
            for(i=0; i<6; i++)
            {
		frm.ans[i].disabled=true;
                if(frm.ans[i].value == "<?=$means[0]?>")
                {
                    frm.ans[i].checked=true;
                    frm.ans[i].className += " correct";
                    break;
                }
            }
            return false;    
        }
        ndx = parseInt(o.value);
        if(ndx>=0 && ndx<6)
            frm.ans[ndx].checked=true;
    }
}
<? } ?>
document.getElementById('focusme').focus();
</script>
<?
#print_r($means);
require_once("foot.php");
?>

<textarea style="display:none">
<?=$_SESSION['buff']?>
</textarea>
