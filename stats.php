<?
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
        $cls = "";
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
