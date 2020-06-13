<?php
//UTF-8
header('Content-Type: text/html; charset=UTF-8');

//사용자로부터 받은 데이터
$data = json_decode(file_get_contents('php://input'), true);
$kakao_key = $data['userRequest']['user']['id'];

//데이터베이스 세팅
$servername = "";       //서버 IP
$username = "";         //DB ID
$password = "";         //DB Password
$dbname = "";           //DB NAME
$conn = mysqli_connect($servername, $username, $password, $dbname);
mysqli_query($conn, 'SET NAMES utf8');

$query = "SELECT * FROM member WHERE userkey = '$kakao_key';";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_row($result)){
    $loadUserKey = $row[0]; // 유저 키 값
    $loadSchoolCode = $row[1]; // 학교 코드 값
    $loadSchoolName = $row[2]; // 학교 이름 값
    $loadMemberType = $row[3]; // 맴버 유형 값
}

switch($loadMemberType){
    case 0: $typeName = "학생"; break;
    case 1: $typeName = "학부모"; break;
    case 2: $typeName = "교사"; break;
    default: $typeName = "당신이 누구인지 등록 해 주세요."; break;
}

if(isset($loadUserKey)) {
    echo '{
        "version": "2.0",
        "template": {
            "outputs": [
                {
                    "simpleText": {
                        "text": "현재 학교 : '.$loadSchoolName.'\\n학교 코드 : '.$loadSchoolCode.'\\n맴버 유형 : '.$typeName.'\\n카카오톡 키 : '.$kakao_key.'"
                    }
                }
            ]
        }
    }';
}
else {
    echo '{
        "version": "2.0",
        "template": {
            "outputs": [
                {
                    "simpleText": {
                        "text": "등록 된 학교가 없습니다.\\n등록/수정 버튼을 눌러 정보를 등록 해 주세요!\\n카카오톡 키 : '.$kakao_key.'"
                    }
                }
            ]
        }
    }';
}
mysqli_close($conn);
?>