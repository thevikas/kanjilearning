<?php
require_once("db.php");
ob_start();

global $debugprinting;
$debugprinting=1;

$halt = 0;

$ans0 = $ans = cleanvarp('ans');
$qid = cleanvarp('id');
$meanslike = cleanvarp('meanslike');
$qt = $_POST['qt'];

echo "<!-- POST DATA -->";
print_r($_POST);

ob_start();
$word = new clsWord();
$word->load($qid);
print_r($word);

$wrongs = $corrects = $status=0;
print_r($_POST);
$and = "";
#checking right or wrong based on question type
# 1 - japanese word with english options
if($qt==1)
{
    #201103231310:vikas:fixed a bug in comparision.
    if(addslashes($word->means) == addslashes($ans))
        $corrects=1;
    else
        $wrongs=1;
    $ans = $word->means;
}
# 2 - japanese word with english textbox
else if($qt==2)
{
    $w_means = strtolower(addslashes($word->means));
    $w_given = strtolower(addslashes($meanslike));
    #space are ignored whike checking, all space removed from both answer and given
    $w_means = str_replace(' ', '', $w_means);
    $w_given = str_replace(' ', '', $w_given);

    if(strstr($w_means,$w_given))
        $corrects=1;
    else
        $wrongs=1;
    $ans = $word->means;
}
# 3 - english word with japanese options
else if($qt==3)
{
    $ans = $word->word;
    $corrects = $word->word == $ans;
}
# 4 - english word with japanese textbox
else if($qt>=4)
{
    $w_japword = strtolower(addslashes($word->word));
    $w_given = strtolower(addslashes($meanslike));

    #201104042127:vikas:to remove junk dots while comparision
    $w_japword = str_replace("・", "", $w_japword);


    if(strstr($w_japword,$w_given))
        $corrects=1;
    else
        $wrongs=1;

    debugprint("[$w_japword] ? [$w_given] $corrects/$wrongs;");

    $ans = $word->word;
}

#handling correct answers
if($corrects)
{

#wrongs can badly change the percentage, therefore a lot of logic can be based on percentage.
#100% is not the only perfect score. 95% (1/12/09) is also good enough.
    echo "<br/>correct";
    $corrects = 1;
    $jump = 2;

    if($word->correct>1 && 'A' == $word->grade)
        $jump = 20;

    if($word->correct>10 && 'A' == $word->grade)
        $jump += 20;

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
        #if($word->percentage>80)
        #    $halt=1;
        if($word->percentage==100 && $word->shown>=3)
            $word->difference+=10;
        else if($word->percentage>80 && $word->shown>5)
            $word->difference+=5;
        else
            $word->difference+=2;
    }
    else if($word->difference >= 10 && $word->correct>=1 && $word->correct<=3)
    {
        echo "03";
        $corrects=1;
    }
    else if($word->difference >= 10)
    {
        echo "07 (jump:$jump,{$word->difference})\n";
        $word->difference += $jump;
    }
    else
    {
        echo "95";
    }
}
else #handling wrong answers
{
    $wrong_diff2 = 0;
    $wrong_diff1 = 1;
    if($word->percentage>90 && $word->shown>15 && $word->correct>10)
    {
        echo "06";
        $wrong_diff2 = 5;
    }

    if($word->wrong>=0 && $word->wrong<=3)
    {
        echo "04";
        $word->difference = $wrong_diff1;
    }
    else if($word->wrong>3)
    {
        echo "05";
        $word->difference = $wrong_diff2;
    }
    else
    {
        echo "96";
    }
    $wrongs=1;
}

$nextdate = time();

if($word->firstdate == 0)
{
    $word->firstdate = time();
    if($word->shown>=5 && $this->grade=='A')
    {
        echo "\nN2";
        $nextdate = time() + 86400;
    }
}
else
{
    if($corrects)
    {
        echo "\nN1";
        $nextdate = time() + 86400;
    }
}

if($quiz_mode==2)
    $nextdate = time();

doqueryi("insert into score(kanji_id,status,dated) values($qid,$status,now())");
doqueryi("insert into sensex(v) select avg(100*correct/shown) from stats");

$r = doqueryi("select * from stats where kanji_id=$qid");

if(!$r->fetch_array())
    doqueryi("INSERT INTO `stats` (`kanji_id`, `shown`, `correct`, `dated`, `wrong`, `difference`, `countdown`) VALUES ($qid,0,0,now(),0,0,0);");

$sql = "";
if($quiz_mode==1)
    $sql = " and nextdate<=date(now())";

//if there are more than 10 in ctr==0 then don't decriment anyone more than difference>10
$r = doqueryi("select count(*) from stats where countdown=0");
if($rs = $r->fetch_array())
{
    if($rs[0] > 25) //changed from 10 on 201104090857:vikas
    {
        //TODO, check this step
        //this should reduce the load on CTR==0
        $sql .= " and difference<=25";
    }
    else
    {   //update everyone that should be

    }
}

doqueryi("update stats set countdown=countdown-1 where countdown>0 $sql");
#201104031340:vikas:created field QT for storing Question Type also in the stats record
doqueryi($sql = "update stats  set 
                dated=now()
                ,shown=shown+1
                ,nextdate=FROM_UNIXTIME($nextdate)
                ,firstdate=from_unixtime({$word->firstdate})
                ,correct=correct+$corrects
                ,difference={$word->difference}
                ,countdown=countdown+{$word->difference}
                ,wrong=wrong+$wrongs
                ,qt=$qt
                
         where kanji_id=$qid");

$_SESSION['buff'] = ob_get_contents();

$location_header = "Location: ./?last=$corrects&means={$ans}&oldid={$word->getID()}&means={$meanslike}&qt={$qt}&ans0={$ans0}";

debugprint("location:" . $location_header);

if(!$halt)
    header($location_header);
?>
<script type="text/javascript">
//setTimeout('window.location.href="./?last=<?=$corrects?>"',5000);
</script>
