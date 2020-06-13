<?php
//UTF-8
header('Content-Type: text/html; charset=UTF-8');

//사용자로부터 받은 데이터
$data = json_decode(file_get_contents('php://input'), true);
$mtype = $data['action']['params']['membertype'];
$kakao_key = $data['userRequest']['user']['id'];

//데이터베이스 세팅
$servername = "";       //서버 IP
$username = "";         //DB ID
$password = "";         //DB Password
$dbname = "";           //DB NAME
$conn = mysqli_connect($servername, $username, $password, $dbname);
mysqli_query($conn, 'SET NAMES utf8');

//변수
$nowDate = date('Y-m-d H:i');

$query = "SELECT * FROM member WHERE userkey = '$kakao_key';";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_row($result)){
    $loadUserKey = $row[0]; // 유저 키 값
}

if (isset($loadUserKey)) { // userkey가 db에 있는지 확인. 있는 경우 맴버 타입 업데이트
    $query = "UPDATE member SET type = '$mtype', mdate = '$nowDate' WHERE userkey = '$loadUserKey';";  
    if(mysqli_query($conn, $query)) { // 업데이트 완료시
        $query = "SELECT * FROM member WHERE userkey = '$loadUserKey';";
        $result = mysqli_query($conn,$query);
        while($row = mysqli_fetch_row($result)){
            $loadSchoolCode = $row[1]; // 스쿨코드 값
            $loadSchoolName = $row[2]; // 학교이름 값
            $loadMemberType = $row[3]; // 맴버유형 값 : 0 = 학생 , 1 = 학부모 , 2 = 교사
        }
        switch($loadMemberType){
            case 0: $typeName = "학생"; break;
            case 1: $typeName = "학부모"; break;
            case 2: $typeName = "교사"; break;
            default: $typeName = "당신이 누구인지 등록 해 주세요."; break;
        }
            echo '{
                "version": "2.0",
                "template": {
                    "outputs": [
                        {
                            "simpleText": {
                                "text": "학교를 성공적으로 변경 하였습니다.\\n현재 학교 : '.$loadSchoolName.'\\n맴버 유형 : '.$typeName.'"
                            }
                        }
                    ]
                }
            }';
        
    }
}
else { // userkey가 db에 있는지 확인. 없는 경우 안내말 출력
    echo '{
        "version": "2.0",
        "template": {
            "outputs": [
                {
                    "simpleText": {
                        "text": "맴버 유형을 선택하기 전\\n첫번째 말풍선의 학교 선택을 먼저 해주세요."
                    }
                }
            ]
        }
    }';
}
mysqli_close($conn);
?>