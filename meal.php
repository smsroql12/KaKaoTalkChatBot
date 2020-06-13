<?php
//UTF-8
header('Content-Type: text/html; charset=UTF-8');

    function get_url($url)
    {
        global $sccode;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://stu.goe.go.kr/sts_sci_md00_001.do?domainCode=J10&schulCode=".$sccode."&schulCrseScCode=2&schulKndScCode=02&ay=");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    function get_lunch($type)
    {
        date_default_timezone_set("Asia/Seoul");
        global $sccode;
        //$sccode = $sccode;

        if($type == 1 && date('j') == date('t'))
        {
            $url = "https://stu.goe.go.kr/sts_sci_md00_001.do?domainCode=J10&schulCode=".$sccode."&schulCrseScCode=2&schulKndScCode=02&ay=".date("y", strtotime("+1 day"))."&mm=".date("m", strtotime("+1 day"));
        }
        else
            $url = "https://stu.goe.go.kr/sts_sci_md00_001.do?domainCode=J10&schulCode=".$sccode."&schulCrseScCode=2&schulKndScCode=02";
    
        $lunch = get_url($url);
        
        //급식표만
        $lunch = explode("<tbody>", $lunch);
        $lunch = explode("</tbody>", $lunch[1]);
        $lunch = $lunch[0];
        
        $lunch = explode("<td", $lunch);
        
        $count = 1;
        
        for($i=1; $i<=35; $i++)
        {
	        $tmp = explode('</div>', $lunch[$i]);
	        $tmp = explode('><', $tmp[0]);
	        $tmp = explode('div>', $tmp[1]);
	        $tmp = str_replace("<br />", "\\n", $tmp[1]);
	        
	        if(is_numeric(substr($tmp, 0, 1)))
	        {
		        $tmp = str_replace("$count\\n", "", $tmp);
		        $tmp = str_replace("[중식]\\n", "", $tmp);
		        $tmp = str_replace('&amp;', '&', $tmp);
		        if(strlen($tmp) < 3)
		        	$tmp = '급식이 없습니다';
		        
		        $days[$count++] = $tmp;
			}
        }
        
        if($type == 0)
        	return "[".date('n')."월 ".date('j')."일 급식]\\n".$days[date('j')];
        else if($type == 1)
        	return "[내일 급식]\\n".$days[date('j') + 1];
        else
        {
            $result .= "[ 🍚 ".date('n')."월 급식 🍚 ]\\n";
	        foreach($days as $day => $lunch)
				$result .= "[".date('n')."월 ".$day."일 급식]\\n".$lunch."\\n\\n";
			
			return $result;
        }
    }
    

    //사용자로부터 받은 데이터
    $data = json_decode(file_get_contents('php://input'), true);
    $kakao_key = $data['userRequest']['user']['id'];
    $mealtype = $data['action']['params']['mealtype'];

    //데이터베이스 세팅
    $servername = "";       //서버 IP
    $username = "";         //DB ID
    $password = "";         //DB Password
    $dbname = "";           //DB NAME
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    $query = "SELECT * FROM member WHERE userkey = '$kakao_key';";
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_row($result)){
        $loadUserKey = $row[0]; // 유저 키 값
        $sccode = $row[1]; // 학교 코드 값
    }
    if (isset($loadUserKey)) { // userkey가 db에 있는지 확인.
        switch($mealtype) {
            case 0: $meal = get_lunch(0); break;
            case 1: $meal = get_lunch(1); break;
            case 2: $meal = get_lunch(2); break;
            default: $meal = "급식 불러오기에 실패 하였습니다.";
        }
        echo '{
            "version": "2.0",
            "template": {
                "outputs": [
                    {
                        "simpleText": {
                            "text": "'.$meal.'"
                        }
                    }
                ]
            }
        }';
    }
    else { // userkey가 db에 있는지 확인. 없는 경우 안내말 출력
        echo '{
            "version": "2.0",
            "template": {
                "outputs": [
                    {
                        "simpleText": {
                            "text": "등록 된 학교가 없습니다. 학교를 등록 해 주세요."
                        }
                    }
                ]
            }
        }';
    }
    mysqli_close($conn);
?>