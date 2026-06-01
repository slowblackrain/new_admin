# Dometopia Project Task List

## [전체 공통] 멀티 트랙 개발 준비
- [x] 프로젝트 규칙(RULES.md) 분석 및 보고 (@Antigravity-1)
- [x] 멀티 트랙 환경 설정 (@Antigravity-1)
    - [x] .gitignore 최적화 (임시 스크립트 제외)
    - [x] `task.md` 초기화 및 작업 할당
    - [x] 작업 전용 브랜치(`feature/setup-multi-track`) 생성 및 이동
- [x] 신규 에이전트 투입 가이드 작성 (@Antigravity-1)

## [모듈별 작업 현황]

### 1. 결제 및 주문 (PortOne Integration)
- [ ] PortOne 가상계좌 웹훅 검증 및 고도화
- [ ] 환불 로직 안정화 (자동 적립금 전환 방지 및 PG 취소 우선)
- [ ] 가상계좌 환불 시 은행 정보 입력 UI/UX 강제화

### 2. 회원 및 관리 (Member & Admin)
- [ ] 회원 매출 카탈로그 분석 및 포인트 적립 로직 구현
- [ ] 어드민 내 '입력' 버튼 누락 이슈 해결
- [x] 우편번호 API(카카오) 복구 및 결제 페이지 연동

### 3. 상품 및 카테고리 (Goods & Category)
- [ ] 카테고리별 상품 미노출 이슈 진단 및 수정
- [ ] 레거시 상품 데이터 매핑 및 동일성(Parity) 검증

### 4. 기타 문서 및 지원
- [ ] 2021-2026 재고 수불부 및 정산 자료 생성
