<?php
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

$stats = clsWord::getStatistics();

$oldid = 0;
if(isset($_REQUEST['oldid']))
    $oldid = $_REQUEST['oldid'];

$word = clsWord::getNextWord($stats['newleft']>0,$oldid);
echo "<!-- corrent word id: {$word->id} -->";
#$word = new clsWord();
#$word->load(328);

if(!$word)
    die("Nothing to do today");

#load lowest id kanji
#$r = $myi->query("select * from vocab where id not in (select kanji_id from score) limit 0,1");
#$rs = $r->fetch_array();
echo "<!-- ";
if($debugprinting)
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

if($word->correct>5 && ($word->grade=='A' || $word->grade=='B'))
{
    debugprint("QT2");
    $qt++; // 2
}

if($word->shown>10)
{
    debugprint("QT3");
    $qt++; // 3
}

if($word->shown>15)
{
    debugprint("QT4");
    $qt++;// 4
}

if($word->shown>20)
{
    debugprint("QT5");
    $qt++; // 5
}

echo "<!-- corrent word QT: {$qt} -->";

switch($qt)
{
    case 1: //with hinting
        {
            $means = clsWord::getRandomWords($word);
            $question = $word->word . " ({$word->kanji})";;
            echo "<!-- case1 -->";
            break;
        }
    case 2: //no hinting
        {
            //nothing
            echo "<!-- case2 -->";
            $question = $word->word . " ({$word->kanji})";
            break;
        }
    case $qt>2: //no options, a textbox to answer in english
        {
            $means = clsWord::getRandomWords($word,0);
            echo "<!-- case3 -->";
            $question = $word->means;
            break;
        }

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
Meaning
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
if($qt==1 || $qt>2)
{
    $ri = rand(1,5);
    for($i=0; $i<5; $i++)
    {
        ?>
        <!-- $ri=<?=$ri?> -->
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
<? if($qt==2) { ?><input autocomplete="off" id="focusme" type="text" name="meanslike"/> <? } ?>
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
