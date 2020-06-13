<?php
//UTF-8
header('Content-Type: text/html; charset=UTF-8');

  function get_url2($url2) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url2);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $result = curl_exec($ch);
      curl_close($ch);

      return $result;
  }
    
  function get_calendar($month2)
  {
    global $sccode;
    date_default_timezone_set("Asia/Seoul");
    $data = get_url2("https://stu.goe.go.kr/sts_sci_sf01_001.do?schulCode=".$sccode."&schulCrseScCode=2&schulKndScCode=02&ay=".$year."&mm=".$month2);
  
    // 일정표만 가져오기
    $pos = strpos($data, '<tbody>') + strlen('<tbody>');
    $pos2 = strpos($data, '</tbody>') + strlen('</tbody>');
    $data = substr($data, $pos, $pos2 - $pos);

    for($i = 0; $i < 35; $i++) {
      $pos = strpos($data, '<div class="textL">') + strlen('<div class="textL">');
      $data = substr($data, $pos, strlen($data) - $pos);

      $pos = strpos($data, "</div>");
      $data_month[$i] = substr($data, 0, $pos);

      //불필요 태그 제거, 문자열 수정
      $data_month[$i] = strip_tags($data_month[$i],'<br><a/><em/>'); 
      $data_month[$i] = trim($data_month[$i]);

      $data_month[$i] = substr_replace($data_month[$i],'일: ',2,0); 
      $data_month[$i] = str_replace('<br />',"", $data_month[$i]);
      $data_month[$i] = str_replace('<br>',"\\n", $data_month[$i]);
      $data_month[$i] = trim(preg_replace('/[\r\n]{2,}/',"",$data_month[$i]));
      $data_month[$i] = preg_replace("/(\s){2,}/", '', $data_month[$i]);

      $data_month[$i] = str_replace('토요휴업일', "", $data_month[$i]);
      $data_month[$i] = str_replace('일:', '일 : ', $data_month[$i]);

      //"방학식" 이라는 문자가 검색되면 "여름방학","겨울방학" 이라는 문자를 삭제하지 않음.
      //"여름방학" 을 없애버리면 "방학식" 중 "식" 밖에 출력이 되지 않음. 
      if(strpos($data_month[$i], "방학식") === false){
        $data_month[$i] = str_replace('여름방학',"",$data_month[$i]);
        $data_month[$i] = str_replace('겨울방학',"",$data_month[$i]);
      }
      
      if(strlen($data_month[$i])>8)
          $str .= "".$month2."월 ".$data_month[$i]."\\n";
    }

    if($str == "")
        $str .= "일정이 없습니다.";

      return "[".$month2."월 학사 일정]\\n".$str;
  }

  function nextmonth() { // 다음달 구하기
    $datetime = new DateTime(date('Y-m-d'));
    $datetime->modify('next month');
    $next_month = $datetime->format('m');
    return $next_month;
  }

  function sem() { // 학기 일정
    $mon = array("00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
    if(Date('n') < 9 && Date('n') > 2){
      $scheduleValue .= "[ 🗓 1학기 학사 일정 🗓 ]"."\\n";
      for($i=3; $i<9; $i++) {
        $scheduleValue .= get_calendar($mon[$i])."\\n";
      }
    }
    else {
      $scheduleValue .= "[ 🗓 2학기 학사 일정 🗓 ]"."\\n";
      for($i=9; $i<13; $i++) {
        $scheduleValue .= get_calendar($mon[$i]);
      }
      for($i=1; $i<3; $i++) {
        $scheduleValue .= get_calendar($mon[$i]);
      }
    }
      echo '{
        "version": "2.0",
        "template": {
            "outputs": [
                {
                    "simpleText": {
                        "text": "'.$scheduleValue.'"
                    }
                }
            ]
        }
    }';
  }

    //사용자로부터 받은 데이터
    $data = json_decode(file_get_contents('php://input'), true);
    $kakao_key = $data['userRequest']['user']['id'];
    $scheduletype = $data['action']['params']['scheduletype'];

    //데이터베이스 세팅
    $servername = "";       //서버 IP
    $username = "";         //DB ID
    $password = "";         //DB Password
    $dbname = "";           //DB NAME
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    //날짜 변수
    $year = Date('Y');
    $month2 = Date("m");

    $query = "SELECT * FROM member WHERE userkey = '$kakao_key';";
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_row($result)){
        $loadUserKey = $row[0]; // 유저 키 값
        $sccode = $row[1]; // 학교 코드 값
    }
    if (isset($loadUserKey)) { // userkey가 db에 있는지 확인.

        switch($scheduletype) {
          case 0: echo '{
                "version": "2.0",
                "template": {
                    "outputs": [
                        {
                            "simpleText": {
                                "text": "'.get_calendar(Date("m")).'"
                            }
                        }
                    ]
                }
            }';
          break; //이번달 일정

          case 1: echo '{
                "version": "2.0",
                "template": {
                    "outputs": [
                        {
                            "simpleText": {
                                "text": "'.get_calendar(nextmonth()).'"
                            }
                        }
                    ]
                }
            }';
          break; // 다음달 일정

          case 2: sem(); break; // 이번 학기 일정

          default: echo '{
                "version": "2.0",
                "template": {
                    "outputs": [
                        {
                            "simpleText": {
                                "text": "스케줄을 불러올 수 없습니다."
                            }
                        }
                    ]
                }
            }';
         break;
        }
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
