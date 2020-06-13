<?php
//UTF-8
header('Content-Type: text/html; charset=UTF-8');

//사용자로부터 받은 데이터
$data = json_decode(file_get_contents('php://input'), true);
$scode = $data['action']['params']['schoolcode'];
$sname = $data['action']['params']['schoolname'];
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

if (isset($loadUserKey)) { // userkey가 db에 있는지 확인. 있는 경우 학교만 업데이트
    $query = "UPDATE member SET schoolcode = '$scode', schoolname = '$sname', mdate = '$nowDate' WHERE userkey = '$loadUserKey';";
    if(mysqli_query($conn, $query)) { // 업데이트 완료시
        $query = "SELECT * FROM member WHERE userkey = '$kakao_key';";
        $result = mysqli_query($conn, $query);
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
    else { // 업데이트 실패시
        echo '{
            "version": "2.0",
            "template": {
                "outputs": [
                    {
                        "simpleText": {
                            "text": "학교 변경에 실패 하였습니다."
                        }
                    }
                ]
            }
        }';
    }
}
else { // userkey가 db에 있는지 확인. 없는 경우 새로 등록
    $query = "INSERT INTO member (userkey, schoolcode, schoolname, type, jdate, mdate) VALUES ('$kakao_key', '$scode', '$sname', 0, '$nowDate', '$nowDate')";
    if(mysqli_query($conn, $query)) { // 등록 완료시
        $query = "SELECT * FROM member WHERE userkey = '$kakao_key';";
        $result = mysqli_query($conn, $query);
        while($row = mysqli_fetch_row($result)){
            $loadSchoolCode = $row[1]; // 스쿨코드 값
            $loadSchoolName = $row[2]; // 학교이름 값
            $loadMemberType = $row[3]; // 맴버유형 값 : 1 = 학생 , 2 = 학부모 , 3 = 교사
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
                            "text": "[회원가입 완료] 축하합니다!\\n계정을 성공적으로 등록 하였습니다.\\n현재 학교 : '.$loadSchoolName.'\\n맴버 유형 : '.$typeName.'"
                        }
                    }
                ]
            }
        }';
    }
    else { // 등록 실패시
        echo '{
            "version": "2.0",
            "template": {
                "outputs": [
                    {
                        "simpleText": {
                            "text": "계정 등록에 실패 하였습니다."
                        }
                    }
                ]
            }
        }';
    }
}
mysqli_close($conn);
?>