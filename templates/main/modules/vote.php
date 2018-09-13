<?php
// ------------------------------------------- Переделать
function module_vote() {
    global $word;

    $res_str = "";

    $query = "SELECT ".prdb."vote_que.id, ".prdb."vote_text.content, DATE_FORMAT(".prdb."vote_que.date_in, '%d.%m.%Y') AS f_date_in
              FROM ".prdb."vote_que, ".prdb."vote_text
              WHERE ".prdb."vote_que.id=".prdb."vote_text.id AND ".prdb."vote_text.what='que'
                AND ".prdb."vote_text.lang='".LANG."'
              ORDER BY ".prdb."vote_que.date_in DESC";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;
    while($row_que = mysql_fetch_assoc($result)) {
        $res_str .= "
        <table border=0 width=100% cellspacing=0 cellpadding=0 class='listing'>
        <tr>
          <th colspan=4>".$row_que['content']."</th>
        <tr>";

        $query = "SELECT ".prdb."vote_ans.id, ".prdb."vote_ans.stat, ".prdb."vote_text.content
                  FROM ".prdb."vote_ans, ".prdb."vote_text
                  WHERE ".prdb."vote_ans.id=".prdb."vote_text.id AND ".prdb."vote_text.what='ans'
                    AND ".prdb."vote_text.lang='".LANG."' AND que_id='".$row_que['id']."'
                  ORDER BY ".prdb."vote_ans.p_id";
        $result1 = mysql_query($query)   or die(mysql_error()."<br>".$query);
        if(!mysql_num_rows($result1)) continue;
        $sum_vote = 0;
        $i = 0;
        while($row = mysql_fetch_assoc($result1)) {
            $res_arr[$i]['text'] = $row['content'];
            $res_arr[$i]['stat'] = $row['stat'];
            $sum_vote += $row['stat'];
            $i++;
        }

        foreach($res_arr as $str_arr){
            $percent = $sum_vote ? $str_arr['stat'] * 100 / $sum_vote : 0;

            $res_str .= "
            <tr>
              <td width='200px'>".$str_arr['text']."</td>
              <td><div class='voteBar' style='width: ".$percent."%'>&nbsp;</div></td>
              </td>
              <td width='80' align=center valign=top>".$str_arr['stat']."</td>
              <td width='80' align=center valign=top>".number_format($percent, 2, ".", "")." %</td>
            </tr>";
        }

        $res_str .= "</table>
        <div style='text-align: right; font-style: italic; font-size: .9em; margin-bottom: 10px; '>
          ".$word[LANG][36].": ".$row_que['f_date_in'].", ".$word[LANG][35].": ".$sum_vote."
        </div>";
    }

    return $res_str;
}
function make_vote($que_id) {
    global $word, $content_arr;

    if(!isset($_COOKIE['vote']) or !$_COOKIE['vote']) {
        $content_arr['ONLOAD'] .= '
        var options = {
            target: "#voteDiv",
            url: "templates/'.USER_TEMPLATE_DIR.'modules/ajax.php"
        };

        $("#voteForm").submit(function() {
            $(this).ajaxSubmit(options);
            return false;
        });

        ';

        $res_str = vote_form($que_id);
        if(empty($res_str)) return;
    }
    else {
        $res_str = vote_result($que_id);
        if(empty($res_str)) return;
    }

    return $res_str;
}
function vote_form($que_id, $error="") {
    global $word;

    $link_all_vote = link_all_vote();
    if(empty($link_all_vote))
        return;

    $res_str = "";

    $query = "SELECT ".prdb."vote_que.type, ".prdb."vote_text.content
              FROM ".prdb."vote_que, ".prdb."vote_text
              WHERE ".prdb."vote_que.id=".prdb."vote_text.id AND ".prdb."vote_text.what='que'
                AND ".prdb."vote_text.lang='".LANG."'
                AND ".prdb."vote_que.id='".$que_id."'
              ORDER BY ".prdb."vote_que.date_in DESC
              LIMIT 1";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;
    $que_type = mysql_result($result, 0, 0);
    $que_str = mysql_result($result, 0, 1);

    $res_str .= "
    <div id='voteDiv'>
    <div class='columnHeader' id='vote-column-header'>".$que_str."</div>
    <div class='newsList' id='voteDiv'>
      ";

    if(!empty($error))
        $res_str .= "
          <div class='alertTextSmall'>".$error."</div>";
    $query = "SELECT ".prdb."vote_ans.id, ".prdb."vote_text.content
              FROM ".prdb."vote_ans, ".prdb."vote_text
              WHERE ".prdb."vote_ans.id=".prdb."vote_text.id AND ".prdb."vote_text.what='ans'
                AND ".prdb."vote_text.lang='".LANG."' AND que_id='".$que_id."'
              ORDER BY ".prdb."vote_ans.p_id";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;
    $res_str .= "
      <div style='margin: 5px 0; '>
        <form name='voteForm' id='voteForm' action='#' method='post'>
        <input name='action' type='hidden' value='process_vote'>
        <input name='que_id' type='hidden' value='".$que_id."'>
        <input name='lang' type='hidden' value='".LANG."'>
      ";
    while($row = mysql_fetch_assoc($result)) {
        $res_str .= "<div class='vote-row'>";
        if($que_type == 'radio')
            $res_str .= "<label><input name='ans' type='radio' value='".$row['id']."' class=radio> ".$row['content']."</label>";
        else
            $res_str .= "<label><input name='ans[]' type='checkbox' class=radio value='".$row['id']."'> ".$row['content']."</label>";
        $res_str .= "</div>";
    }
    $res_str .= "
        <div align='center'>
          <input type='submit' value=' ".$word[LANG][32]." ' class='button'><br>
        </div>
        </form>
        ".$link_all_vote."
      </div>";
    if(!empty($error)) {
        $res_str .= "
        <script type='text/javascript'>
            var options = {
                target: '#voteDiv',
                url: 'templates/".USER_TEMPLATE_DIR."modules/ajax.php'
            };

            $('#voteForm').submit(function() {
                $(this).ajaxSubmit(options);
                return false;
            });
        </script>
        ";
    }

    return $res_str."</div></div>";
}
function vote_result($que_id) {
    global $word;

    $res_str = "";

    $query = "SELECT content
              FROM ".prdb."vote_text
              WHERE ".prdb."vote_text.what='que'
                AND ".prdb."vote_text.lang='".LANG."'
                AND id='".$que_id."'";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;
    $que_str = mysql_result($result, 0);

    $res_str .= "
    <div class='columnHeader' id='vote-column-header'>".$que_str."</div>
    <div class='newsList' id='voteDiv'>
      ";

    $query = "SELECT ".prdb."vote_ans.id, ".prdb."vote_ans.stat, ".prdb."vote_text.content
              FROM ".prdb."vote_ans, ".prdb."vote_text
              WHERE ".prdb."vote_ans.id=".prdb."vote_text.id AND ".prdb."vote_text.what='ans'
                AND ".prdb."vote_text.lang='".LANG."' AND que_id='".$que_id."'
              ORDER BY ".prdb."vote_ans.p_id";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;
    $res_str .= "<div style='margin: 5px 0; '>";
    $sum_vote = 0;
    $i = 0;
    while($row = mysql_fetch_assoc($result)) {
        $res_arr[$i]['text'] = $row['content'];
        $res_arr[$i]['stat'] = $row['stat'];
        $sum_vote += $row['stat'];
        $i++;
    }

    $res_str .= "
    <table border=0 cellspacing=0 cellpadding=2 width='250' style='margin-left: 5px; '>";
    foreach($res_arr as $str_arr){
        $percent = $sum_vote ? $str_arr['stat'] * 100 / $sum_vote : 0;
        $res_str .= "
        <tr>
          <td class='a-left'>".$str_arr['text']."</td>
          <td width='20'>".$str_arr['stat']."</td>
          <td class='a-left' width='60'>".number_format($percent, 2, ".", "")." %</td>
        </tr>";
    }

    $link_all_vote = link_all_vote();

    $res_str .= "
        <tr>
          <td class='a-left'><b>".$word[LANG][35]."</b></td>
          <td><b>".$sum_vote."</b></td>
          <td>&nbsp;</td>
        </tr>
    </table>
    ".$link_all_vote."</div>";

    return $res_str."</div>";
}
function link_all_vote() {
    global $ini_arr, $word;
    $query = "SELECT alias FROM ".prdb."menu WHERE type='vote'";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;
    $vote_url = $ini_arr['pr_lang'].mysql_result($result, 0).".htm";

    return "<div align='center'><a href='".$vote_url."'>".$word[LANG][33]."</a></div>";

}
?>