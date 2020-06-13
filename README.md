# KaKaoTalkChatBot
카카오톡 플러스친구 학교 급식 / 학사일정 조회 챗봇

이 코드는 카카오톡 플러스친구 채널 생성 & 카카오 I 오픈빌더와 연동이 완료 된 상태임을 가정하고 시작합니다.  
아래 코드는 파싱할 URL의 주소 입니다.
```
https://stu.goe.go.kr/sts_sci_sf01_001.do?schulCode=".$sccode."&schulCrseScCode=2&schulKndScCode=02&ay=
```
파싱할 URL의 파라미터는 다음과 같습니다.
GET/POST| | |
:---|:---|:---
GET|countryCode|교육청 코드|
GET|schulCode|학교코드|
GET|insttNm|학교이름|
GET|schulCrseScCode|학교종류코드|
GET|schMmealScCode|급식종류코드|
GET|schYmd|날짜|


## DB Setting
<div>
<img width="" src="https://user-images.githubusercontent.com/30662770/84564766-54109680-ad9f-11ea-819d-80dabffb57be.png"/>
</div>

자신의 웹 서버에 DB를 구축 한 후 위와 같은 테이블을 생성합니다.

## 회원 가입 스킬 생성
<div>
<img width="" src="https://user-images.githubusercontent.com/30662770/84564854-de58fa80-ad9f-11ea-900a-1531bb3f7053.png"/>
</div>
카카오 I 오픈빌더에서 "스킬" 을 생성한 뒤 사진과 같이 작성 해 줍니다.   
URL 부분은 자신의 웹 서버 경로 중 SignType.php 로 연결 해 주세요.

## 회원 가입 시나리오 생성
<div>
<img width="" src="https://user-images.githubusercontent.com/30662770/84564816-9cc84f80-ad9f-11ea-8dab-c79b0d889586.png"/>
</div>
카카오 I 오픈빌더에서 "시나리오" 를 생성합니다.  
schoolcode 는 학교 고유 코드, schoolname 은 학교 이름 문자열 값 입니다.
학교 코드 조회는 아래의 링크에서 학교를 검색하면 학교의 고유 코드가 검색됩니다.

### 바로가기
[학교코드조회](http://jubsoo2.bscu.ac.kr/src_gogocode/src_gogocode.asp) 
