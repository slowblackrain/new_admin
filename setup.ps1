# 도매토피아 프로젝트 초기 세팅 스크립트 (Windows용)

Write-Host "--- 도매토피아 프로젝트 세팅을 시작합니다 ---" -ForegroundColor Cyan

# 1. .env 파일 생성
if (-not (Test-Path ".env")) {
    Write-Host "[1/4] .env 파일을 생성합니다 (example 복사)..."
    Copy-Item ".env.example" ".env"
} else {
    Write-Host "[1/4] .env 파일이 이미 존재합니다. 건너뜁니다."
}

# 2. Composer 패키지 설치
Write-Host "[2/4] Composer 패키지를 설치합니다..."
composer install

# 3. NPM 패키지 설치
Write-Host "[3/4] NPM 패키지를 설치합니다..."
npm install

# 4. 애플리케이션 키 생성
Write-Host "[4/4] Laravel 애플리케이션 키를 생성합니다..."
php artisan key:generate

Write-Host "--- 세팅이 완료되었습니다. legacy_source 폴더가 상위 디렉토리에 있는지 확인하세요. ---" -ForegroundColor Green
