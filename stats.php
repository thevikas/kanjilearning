<?
$r = doqueryi("select *,100*correct/shown as `percent` from stats");

$level = 13;
if(!isset($_SESSION['ctrd']))
    $ctrda = array();
else
    $ctrda = $_SESSION['ctrd'];
?>
<table border=1 align=right class="rep cright">
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
</table>

<div style="float:right; clear:both;" class="stt">
<?
while(count($ctrda)>10)
{
    foreach($ctrda as $key => $v)
    {
        unset($ctrda[$key]);
        break;
    }
    //die;
}
echo implode($ctrda,",");
?>
</div>
<br/>
<?
$rc=0;
$key_total = array();
while($rs = $r->fetch_array())
{
        $cls = "";
        if($rc %27 ==0)
        {
            ?>
            </table>
            <table id="r<?=$rc?>" class="stt" border="1">
            <tr>
            <td>#</td>
            <?
            foreach($rs as $key => $val)
            {
                if(is_numeric($key))
                    continue;
                if($key == 'countdown')
                    $key = 'ctrd';
                if($key == 'difference')
                    $key = 'diff';
                if($key == 'kanji_id')
                    $key = 'id';
                if($key == 'shown')
                    $key = 'sh.';
                if($key == 'correct')
                    $key = 'cr.';
                if($key == 'wrong')
                    $key = 'wr.';
                
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
        if($rs['kanji_id'] == $word->id)
            $cls = "current";
        else if($rs['kanji_id'] == cleanvarg("oldid",0))
            $cls = "before";

        if(isset($_REQUEST['oldid']) && $_REQUEST['oldid']==$rs['kanji_id'])
            $cls .= " oldid";
        //if(strtotime($rs['nextdate'])>)
        $nexttime = strtotime($rs['nextdate']);
        $nextdatestring = date("Y-m-d",$nexttime);

        $rc++;
        if($todaystring != $nextdatestring && $nexttime>time() )
            continue;
        ?>
        <tr class="<?=$cls?>" id="k<?=$rs['kanji_id']?>">
        <td title="<?=$nextdatestring?>"><?=$rc?></td>
        <?
        foreach($rs as $key => $val)
        {
            if(is_numeric($key))
                continue;
            if(strstr($key,"date") || strstr($key,"percent"))
                continue;

            if(!isset($key_total[$key]))
                $key_total[$key] = 0;
            $key_total[$key] += $val;

           ?>
            <td>
            <?=$val?>
            </td>
            <?
            
        }
        ?>
    </tr>
    <?
            //print_r($key_total);

}


    ?><tr class="<?=$cls?> totals">
        <td title="total">T</td>
        <?
    foreach($key_total as $key => $val)
    {
           ?>
            <td>
            <?=$key_total[$key]?>
            </td>
            <?
    }
        ?>
    </tr>
</table>
<?
if(!isset($_SESSION['ctrd']))
{
    $_SESSION['ctrd'] = array();
}
$ctrda[] = $key_total['countdown'];
$_SESSION['ctrd'] = $ctrda;

?>
<div id="stats">
<label>Seen Today:</label><?=$stats['today']?><br/>
<label>Session Duraton:</label><?=$stats['session']?><br/>
<label>New Today:</label><?=$stats['new']?><br/>
<label>New Left:</label><?=$stats['newleft']?><br/>
<label>Sensex:</label><?=$stats['sensex']?><br/>
<label>This word:</label><?=$word->percentage?><br/>
<label class="grade grade<?=$word->grade?>"><?=$word->grade?></label>
</div>


<label title="<?$means?>">Last Answer: <?
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

