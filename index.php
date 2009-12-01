<?php
require_once("db.php");

$word = clsWord::getNextWord();

#load lowest id kanji
#$r = $myi->query("select * from vocab where id not in (select kanji_id from score) limit 0,1");
#$rs = $r->fetch_array();

#print_r($word);


$qt=1;

if($word->percentage>=80 && $word->correct>5)
    $qt=2;

if($qt==1)
    $means = clsWord::getRandomWords($word->means);


require_once("header.php");
$r = doqueryi("select v from sensex order by dated desc limit 0,5");
?><br/><?
while($rs = $r->fetch_object())
{
    echo "{$rs->v},";
}
?><br/><?
$r = doqueryi("select *,100*correct/shown as `percent` from stats");
?>
<table border=1 align=right class="rep">
<?
$rc=0;
while($rs = $r->fetch_array())
{
        if($rc==0)
        {
            ?>
            <tr>
            <?
            foreach($rs as $key => $val)
            {
                if(is_numeric($key))
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
        ?>
        <tr class="<?=$cls?>">
        <?
        foreach($rs as $key => $val)
        {
            if(is_numeric($key))
                continue;
           ?>
            <td>
            <?=$val?>
            </td>
            <?
        }
        $rc++;
        ?>
    </tr>
    <?
}
?>
</table>
Last Answer: <? 
    if(isset($_GET['last']) )
    {
        if($_GET['last']==1) 
            echo "Correct"; 
        else
        {
            ?><font color=red>Wrong</font><?
        }
    }
?>
<form method="post" action="check.php">
<input type="hidden" name="id" value="<?=$word->id?>"/>
<p class="w">
<label>Word</label>
<strong>
<?=$word->word?>
</strong>
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


<?php
if($qt==1)
{
    $ri = rand(1,5);
    for($i=0; $i<5; $i++)
    {
        ?>
        <input type="radio" id="r<?=$i?>" name="ans" value="<?=$means[$ri % 5]?>"/>
        <label for="r<?=$i?>"><?=$i?>: <?=$means[$ri % 5]?></label><br/>
        <?    
        $ri++;
    }
    ?>
    <input type="text" value="" id="focusme" onkeypress="return kp(event,this)"/>
    <?
}
?>
<input type="hidden" name="qt" value="<?=$qt?>"/>
<? if($qt==2) { ?><input id="focusme" type="text" name="meanslike"/> <? } ?>
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
