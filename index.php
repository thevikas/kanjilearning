<?php
require_once("db.php");
global $debugprinting;
$debugprinting=1;

global $todaystring;

$todaystring = date("Y-m-d",time());

$word = clsWord::getNextWord();
if(!$word)
    die("Nothing to do today");
$stats = clsWord::getStatistics();

#load lowest id kanji
#$r = $myi->query("select * from vocab where id not in (select kanji_id from score) limit 0,1");
#$rs = $r->fetch_array();
echo "<!-- ";
if($debugprinting)
    print_r($word);
echo "-->";

$qt=1;

if($word->percentage>=80 && $word->correct>5)
    $qt=2;

if($qt==1)
    $means = clsWord::getRandomWords($word->means);


require_once("header.php");

$r = doqueryi("select *,100*correct/shown as `percent` from stats");

$level = 13;
?>
<table border=1 align=right class="rep">
<tr>
<td colspan="10">
Queue Size for Level <?=$level?> is <big><strong><?=clsWord::getQueueStats($level)?></strong></big>

<?=clsWord::getQueueStats(4)?>,
<?=clsWord::getQueueStats(3)?>,
<?=clsWord::getQueueStats(2)?>,
<?=clsWord::getQueueStats(1)?>,
<?=clsWord::getQueueStats(0)?>
</td>
</tr>
<?
$rc=0;
while($rs = $r->fetch_array())
{
        if($rc==0)
        {
            ?>
            <tr>
            <td>#</td>
            <?
            foreach($rs as $key => $val)
            {
                if(is_numeric($key))
                    continue;
                if(strstr($key,"date") || strstr($key,"percent"))
                    continue;
               ?>
                <td>
                <?=$key?>
                </td>
                <?
            }
            ?>
            </tr>
            <?
        }
        $cls = "";
        if($rs['kanji_id'] == $word->id)
            $cls = "current";
        else if($rs['kanji_id'] == cleanvarg("oldid",0))
            $cls = "before";
        //if(strtotime($rs['nextdate'])>)
        $nexttime = strtotime($rs['nextdate']);
        $nextdatestring = date("Y-m-d",$nexttime);

        $rc++;
        if($todaystring != $nextdatestring && $nexttime>time() )
            continue;
        ?>
        <tr class="<?=$cls?>">
        <td title="<?=$nextdatestring?>"><?=$rc?></td>
        <?
        foreach($rs as $key => $val)
        {
            if(is_numeric($key))
                continue;
            if(strstr($key,"date") || strstr($key,"percent"))
                continue;

           ?>
            <td>
            <?=$val?>
            </td>
            <?
        }
        ?>
    </tr>
    <?
}
?>
</table>

<div id="stats">
<label>Seen Today:</label><?=$stats['today']?><br/>
<label>New Today:</label><?=$stats['new']?><br/>
<label>New Left:</label><?=$stats['newleft']?><br/>
<label>Sensex:</label><?=$stats['sensex']?><br/>
<label>This word:</label><?=$word->percentage?><br/>
<label class="grade grade<?=$word->grade?>"><?=$word->grade?></label>
</div>


<label title="<?=$_GET['means']?>">Last Answer: <? 
    if(isset($_GET['last']) )
    {
        if($_GET['last']==1) 
            echo "Correct"; 
        else
        {
            ?><font color=red>Wrong</font><?
        }
    }

$cls = "";
if($word->shown==0)
    $cls = " class=\"new\" ";

?></label/>

<form method="post" action="check.php">
<input type="hidden" name="id" value="<?=$word->id?>"/>
<p class="w">
<label>Word</label>
<strong <?=$cls?>>
<?=$word->word?>
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
<?
print_r($means);
?>
-->

<?php
if($qt==1)
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
