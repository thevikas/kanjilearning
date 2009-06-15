<?php
require_once("db.php");

#load lowest id kanji
$r = $myi->query("select * from vocab where id not in (select kanji_id from score) limit 0,1");
$rs = $r->fetch_array();


#select 5 random meanings
$ids = array();
for($i=0; $i<5; $i++)
{
    $ids[]=rand(1,720);
}
$idstring = join(',',$ids);

$r2 = $myi->query("select means from vocab where id in ($idstring)");
$means = array();
$means[] = $rs['means'];
while($rs2 = $r2->fetch_array())
{
	$means[] = $rs2['means'];
}

require_once("header.php");
?>
<form method="post" action="check.php">
<input type="hidden" name="id" value="<?=$rs['id']?>"/>
<p class="w">
<label>Word</label>
<strong>
<?=$rs['word']?>
</strong>
</p>
<p class="m">
<label>
Meaning
</label>
<?
if(0)
{
    ?>
    <strong>
    <?=$rs['means']?>
    </strong>
    <?
}
?></p>


<?php
$ri = rand(1,6);
for($i=0; $i<6; $i++)
{
    ?>
    <input type="radio" id="r<?=$i?>" name="ans" value="<?=$means[$ri % 6]?>"/>
    <label for="r<?=$i?>"><?=$i?>: <?=$means[$ri % 6]?></label><br/>
    <?    
    $ri++;
}
?>
<input type="text" value="" id="focusme" onkeypress="return kp(event,this)"/>
<input type="submit"/>
</form>
<script>
function kp(e,o)
{
    frm = o.form;
    if(e.keyCode==13)
    {
        if(o.value == '?')
        {
            for(i=0; i<6; i++)
            {
                if(frm.ans[i].value == '<?=$means[0]?>')
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
document.getElementById('focusme').focus();
</script>
<?
require_once("foot.php");
?>
