<?php
require_once("db.php");
$ans = $_POST['ans'];
$qid = $_POST['id'];
$meanslike = $_POST['meanslike'];
$qt = $_POST['qt'];

ob_start();
$word = new clsWord();
$word->load($qid);
print_r($word);

$wrongs = $corrects = $status=0;
if($qt==1)
{
    if($word->means == $ans)
        $corrects=1;
    else
        $wrongs=1;
}
else if($qt==2)
{
    if(strstr($word->means,$meanslike))
        $corrects=1;
    else
        $wrongs=1;
}

if($corrects)
{
    $corrects = 1;
    $jump = 2;
    if($word->correct>15)
        $jump+=5;
    if($word->correct>20)
        $jump+=10;
    if($word->correct>30)
        $jump+=15;

    if($word->shown==0)
    {
        echo "01";
        $word->difference = 10;
    }
    else if($word->difference < 10)
    {
        echo "02";
        $word->difference+=2;
    }
    else if($word->difference >= 10 && $word->correct>=1 && $word->correct<=3)
    {
        echo "03";
        $corrects=1;
    }
    else if($word->difference >= 10)
    {
        $word->difference += $jump;
    }
    else
    {
        echo "95";
    }
}
else
{
    if($word->wrong>=0 && $word->wrong<=3)
    {
        echo "04";
        $word->difference = 1;
    }
    else if($word->wrong>3)
    {
        echo "05";
        $word->difference = 0;
    }
    else
    {
        echo "96";
    }
    $wrongs=1;
}

$myi->query("insert into score(kanji_id,status,dated) values($qid,$status,now())");

$r = $myi->query("select * from stats where kanji_id=$qid");
if(!$r->fetch_array())
    doqueryi("INSERT INTO `stats` (`kanji_id`, `shown`, `correct`, `dated`, `wrong`, `difference`, `countdown`) VALUES ($qid,0,0,now(),0,0,0);");
$myi->query("update stats set countdown=countdown-1 where countdown>0");
$myi->query($sql = "update stats  set 
                dated=now()
                ,shown=shown+1
                ,correct=correct+$corrects
                ,difference={$word->difference}
                ,countdown=countdown+{$word->difference}
                ,wrong=wrong+$wrongs
                
         where kanji_id=$qid");
echo "<br/><br/>" . $sql;
#header("Location: ./?last=$corrects");
?>
<script type="text/javascript">
setTimeout('window.location.href="./?last=<?=$corrects?>"',5000);
</script>
