<?php

class clsWord
{
    var $id;
    var $word;
    var $means;
    var $shown;
    var $percentage;
    var $correct;
    var $wrong;
    /*
     * countdown jumpt difference currently assigned
     */
    var $difference;
    /*
     * When was it shown first time
     */
    var $firstdate;
    var $dated;
    /*
     * Countdown
     */
    var $countdown;


    static function getNextWord($newword=1,$oldid=0)
    {
        global $myi;
        global $new_kanji_ratio;
        global $old_kanji_ratio;
        #first get kanji with countdown=ZERO
        $r = doqueryi("SELECT id FROM vocab v
                         JOIN stats s ON s.kanji_id=v.id
                        AND date(nextdate)<=DATE(NOW())
                        AND countdown=0
                     LIMIT 0,$old_kanji_ratio");
        $ids = array();

        while($rs = $r->fetch_array())
        {
             debugprint("\n<!-- old -->");
            $ids[] = $rs['id'];
        }

        if($newword)
        {
            #first get a new kanji
            $r = doqueryi("SELECT id,countdown FROM vocab v
                             left JOIN stats s ON s.kanji_id=v.id
                                where book=10 and v.id != $oldid and countdown IS NULL
                                order by rand()
                         LIMIT 0,$new_kanji_ratio");

            while($rs = $r->fetch_array())
            {
                debugprint("\n<!-- new -->");
                if($rs['countdown']>0)
                    throw new Exception("lovely! countdown was ZERO?");
                $ids[] = $rs['id'];
            }
        }
        echo "<!-- ids array="; print_r($ids); echo "-->";
        if(count($ids)==0) return false;
        $ctr = count($ids);
        $r_id = $ids[rand(0,$ctr-1)];
    
        $word = new clsWord();
        $word->load($r_id);
        return $word;
    }

    static function getRandomWords($word,$bEnglishOptions=1)
    {
        debugprint(__FUNCTION__ . ":english:$bEnglishOptions");
        #select 5 random meanings
        $ids = array();
        if($word->percentage>=50)
        {
            $r = doqueryi("select kanji_id from stats order by rand() limit 0,4");
            while($rs = $r->fetch_array())
            {
                $ids[] = $rs[0];
            }
        }
        else
        {
            for($i=0; $i<4; $i++)
            {
                $ids[]=rand(1,720);
            }
        }
        $idstring = join(',',$ids);

        $r2 = doqueryi("select means,word,word2 from vocab where id in ($idstring)");
        $means = array();

        $kanji  = "";
        if(!empty($word->kanji))
            $kanji = " ({$word->kanji})";
            
        //fill the answer with correct answer
        if($bEnglishOptions)
            $means[] = $word->means;
        else
            $means[] = "{$word->word}{$kanji}";

        while($rs2 = $r2->fetch_array())
        {
            //fill with other wrong answer
            $kanji  = "";
            if(!empty($rs2['word2']))
                $kanji = " ({$rs2['word2']})";

	        if($bEnglishOptions)
                $means[] = $rs2['means'];
            else
                $means[] = $rs2['word'] . "$kanji";
        }
        echo "<!--"; print_r($means); echo "--?>";
        return $means;

    }

    function load($id)
    {
        global $myi;
        $r = doqueryi("select * from vocab v
                 left join stats s on s.kanji_id=v.id
                 where id=$id");
        if($rs = $r->fetch_array())
        {
            $this->id = $rs['id'];
            $this->means = $rs['means'];
            $this->word = $rs['word'];
            $this->kanji = $rs['word2'];

            $this->book = $rs['book'];

            $this->dated = $rs['dated'];
            $this->shown = 0 + $rs['shown']; #how many times shown
            $this->correct = 0 + $rs['correct']; #how many times correctly answered
            $this->wrong = 0 + $rs['wrong']; #wrongly answered
            $this->difference = 0 + $rs['difference']; #the current jump difference
            $this->countdown = 0 + $rs['countdown']; #the countdown, counted after every attempt of any word
            $this->firstdate = $rs['firstdate'] == '0000-00-00 00:00:00' ? 0 : strtotime($rs['firstdate']); 
            $this->percentage = 0; #calculated percentage of correct answers
            if($this->shown>0)
                $this->percentage = round(100*$this->correct/$this->shown);

            if($this->percentage>=90)
                $this->grade = 'A';
            else if($this->percentage>=70)
                $this->grade = 'B';
            else if($this->percentage>=40)
                $this->grade = 'C';
            else if($this->percentage<40)
                $this->grade = 'D';
            return true;
        }
        die("not found clsword:$id");
    }

    public function getID()
    {
        return $this->id;
    }

    static function getQueueStats($level=1)    
    {
        $target_count = 0;
        $total_queue = 0;
        $ctr=0;
        $last_total_queue = -1;
        for($i=0; $i<=$level; $i++)
        {

            $ctr = getcount("select count(*) from stats where unix_timestamp(nextdate)<=now() && countdown<=$total_queue && countdown>$last_total_queue");
            echo "\n<!-- $ctr @ $target_count -->\n";
            
            if($ctr==0)
                break;
            $last_total_queue = $total_queue;
            $total_queue += $ctr;
            $targecout_count = $ctr;
        }
        return $total_queue;
    }

    static function getStatistics()
    {
        global $quiz_mode;
        //how many words attempted today, how many new, how many new words left
        $sql = "SELECT count( * )
                FROM `stats`
                WHERE date( dated ) = date( now( ) ) ";
        $rt['today'] = getcount($sql);

        $sql = "SELECT count( * )
                FROM `stats`
                WHERE date( firstdate ) = date( now( ) ) ";
        $rt['new'] = getcount($sql);

        if($quiz_mode==2)
            $rt['newleft'] = 10;
        else
            $rt['newleft'] = 10 - $rt['new'];
        
        $rt['sensex'] = getcount("select v from sensex order by dated desc limit 0,1");

        if(isset($_SESSION['started']))
        {
            $ses_length = time() - $_SESSION['started'];
            $s_mins = intval($ses_length / 60);
            $s_secs = $ses_length % 60;
            $rt['session'] = "$s_mins:$s_secs";
        }

        return $rt;


    }
}


?>
