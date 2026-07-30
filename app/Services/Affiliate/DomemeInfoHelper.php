<?php

namespace App\Services\Affiliate;

class DomemeInfoHelper
{
    /**
     * 상품군(goods_sub_info)에 따라 도매꾹용 정보고시 데이터를 변환하여 반환
     *
     * @param object $goods 상품 모델 객체
     * @return array ['infoDutyType' => int, 'infoDuty' => string]
     */
    public static function getInfoDuty($goods)
    {
        $infoDutyType = 0;
        $infoDuty = "";
        
        $subInfoNum = $goods->goods_sub_info;
        
        // JSON 디코딩 (레거시 sub_info_desc 필드)
        $tmp_info = json_decode($goods->sub_info_desc, true);
        if (!$tmp_info) {
            $tmp_info = [];
        }

        // 도우미 함수: 키 값이 없으면 '상세참조' 반환
        $getVal = function ($key) use ($tmp_info) {
            return !empty($tmp_info[$key]) ? $tmp_info[$key] : "상세참조";
        };

        if ($subInfoNum == 1) { // 의류
            $infoDutyType = 1;
            $infoDuty = "1:" . $getVal('제품소재') . PHP_EOL .
                        "2:" . $getVal('색상') . PHP_EOL .
                        "3:" . $getVal('치수') . PHP_EOL .
                        "4:" . $getVal('제조자/수입자') . PHP_EOL .
                        "5:" . $getVal('제조국') . PHP_EOL .
                        "6:" . $getVal('세탁방법 및 취급시 주의사항') . PHP_EOL .
                        "7:" . $getVal('제조연월') . PHP_EOL .
                        "8:" . $getVal('품질보증기준') . PHP_EOL .
                        "9:" . $getVal('A/S책임자와 전화번호');
        } else if ($subInfoNum == 2) { // 구두/신발
            $infoDutyType = 2;
            $infoDuty = "1:" . $getVal('제품 주소재') . PHP_EOL .
                        "2:" . $getVal('색상') . PHP_EOL .
                        "3:" . $getVal('치수') . PHP_EOL .
                        "4:" . $getVal('제조자') . PHP_EOL .
                        "5:" . $getVal('제조국') . PHP_EOL .
                        "6:" . $getVal('취급시 주의사항') . PHP_EOL .
                        "7:" . $getVal('품질보증기준') . PHP_EOL .
                        "8:" . $getVal('A/S책임자와 전화번호');
        } else if ($subInfoNum == 3) { // 가방
            $infoDutyType = 3;
            $infoDuty = "1:" . $getVal('종류') . PHP_EOL .
                        "2:" . $getVal('소재') . PHP_EOL .
                        "3:" . $getVal('색상') . PHP_EOL .
                        "4:" . $getVal('크기') . PHP_EOL .
                        "5:" . $getVal('제조자/수입자') . PHP_EOL .
                        "6:" . $getVal('제조국') . PHP_EOL .
                        "7:" . $getVal('취급시 주의사항') . PHP_EOL .
                        "8:" . $getVal('품질보증기준') . PHP_EOL .
                        "9:" . $getVal('A/S책임자와 전화번호');
        } else if ($subInfoNum == 4) { // 패션잡화
            $infoDutyType = 4;
            $infoDuty = "1:" . $getVal('종류') . PHP_EOL .
                        "2:" . $getVal('소재') . PHP_EOL .
                        "3:" . $getVal('치수') . PHP_EOL .
                        "4:" . $getVal('제조자/수입자') . PHP_EOL .
                        "5:" . $getVal('제조국') . PHP_EOL .
                        "6:" . $getVal('취급시 주의사항') . PHP_EOL .
                        "7:" . $getVal('품질보증기준') . PHP_EOL .
                        "8:" . $getVal('A/S책임자와 전화번호');
        } else if ($subInfoNum == 5) { // 침구류/커튼
            $infoDutyType = 5;
            $infoDuty = "1:" . $getVal('제품소재') . PHP_EOL .
                        "2:" . $getVal('색상') . PHP_EOL .
                        "3:" . $getVal('치수') . PHP_EOL .
                        "4:" . $getVal('제품구성') . PHP_EOL .
                        "5:" . $getVal('제조자/수입자') . PHP_EOL .
                        "6:" . $getVal('제조국') . PHP_EOL .
                        "7:" . $getVal('세탁방법 및 취급시 주의사항') . PHP_EOL .
                        "8:" . $getVal('품질보증기준') . PHP_EOL .
                        "9:" . $getVal('A/S 책임자와 전화번호');
        } else if ($subInfoNum == 6) { // 가구
            $infoDutyType = 6;
            $infoDuty = "1:" . $getVal('품명') . PHP_EOL .
                        "2:" . $getVal('KC 인증정보') . PHP_EOL .
                        "3:" . $getVal('색상') . PHP_EOL .
                        "4:" . $getVal('구성품') . PHP_EOL .
                        "5:" . $getVal('주요소재') . PHP_EOL .
                        "6:" . $getVal('제조자/수입자') . PHP_EOL .
                        "7:" . $getVal('제조국') . PHP_EOL .
                        "8:" . $getVal('크기') . PHP_EOL .
                        "9:" . $getVal('재공급 사유 및 하자 대처 정보') . PHP_EOL .
                        "10:" . $getVal('배송/설치비용') . PHP_EOL .
                        "11:" . $getVal('품질보증기준') . PHP_EOL .
                        "12:" . $getVal('A/S책임자와 전화번호');
        } else if ($subInfoNum == 7) { // 영상가전
            $infoDutyType = 7;
            $infoDuty = "1:" . $getVal('품명 및 모델명') . PHP_EOL .
                        "2:" . $getVal('KC 인증정보') . PHP_EOL .
                        "3:" . $getVal('정격전압,소비전력') . PHP_EOL .
                        "4:" . $getVal('에너지소비효율등급') . PHP_EOL .
                        "5:" . $getVal('동일모델의 출시년월') . PHP_EOL .
                        "6:" . $getVal('제조자/수입자') . PHP_EOL .
                        "7:" . $getVal('제조국') . PHP_EOL .
                        "8:" . $getVal('크기, 형태') . PHP_EOL .
                        "9:" . $getVal('화면사양') . PHP_EOL .
                        "10:" . $getVal('품질보증기준') . PHP_EOL .
                        "11:" . $getVal('AS책임자와 전화번호') . PHP_EOL .
                        "12:" . $getVal('추가설치비용');
        } else if ($subInfoNum == 8) { // 가정용 전기제품
            $infoDutyType = 8;
            $infoDuty = "1:" . $getVal('품명 및 모델명') . PHP_EOL .
                        "2:" . $getVal('KC 인증정보') . PHP_EOL .
                        "3:" . $getVal('정격전압,소비전력') . PHP_EOL .
                        "4:" . $getVal('에너지소비효율등급') . PHP_EOL .
                        "5:" . $getVal('동일모델의 출시년월') . PHP_EOL .
                        "6:" . $getVal('제조자/수입자') . PHP_EOL .
                        "7:" . $getVal('제조국') . PHP_EOL .
                        "8:" . $getVal('크기, 용량, 형태') . PHP_EOL .
                        "9:" . $getVal('품질보증기준') . PHP_EOL .
                        "10:" . $getVal('AS책임자와 전화번호');
        } else if ($subInfoNum == 9) { // 계절가전
            $infoDutyType = 9;
            $infoDuty = "1:" . $getVal('품명 및 모델명') . PHP_EOL .
                        "2:" . $getVal('KC 인증정보') . PHP_EOL .
                        "3:" . $getVal('정격전압,소비전력') . PHP_EOL .
                        "4:" . $getVal('에너지소비효율등급') . PHP_EOL .
                        "5:" . $getVal('동일모델의 출시년월') . PHP_EOL .
                        "6:" . $getVal('제조자/수입자') . PHP_EOL .
                        "7:" . $getVal('제조국') . PHP_EOL .
                        "8:" . $getVal('크기, 형태') . PHP_EOL .
                        "9:" . $getVal('냉난방면적') . PHP_EOL .
                        "10:" . $getVal('추가설치비용') . PHP_EOL .
                        "11:" . $getVal('품질보증기준') . PHP_EOL .
                        "12:" . $getVal('AS책임자와 전화번호');
        } else {
            // 기본값 (기타 용품)
            $infoDutyType = 35;
            $infoDuty = "1:" . $getVal('품명 및 모델명') . PHP_EOL .
                        "2:" . $getVal('법에 의한 인증, 허가 등을 받았음을 확인할 수 있는 경우 그에 대한 사항') . PHP_EOL .
                        "3:" . $getVal('제조국 또는 원산지') . PHP_EOL .
                        "4:" . $getVal('제조자, 수입품의 경우 수입자를 함께 표기') . PHP_EOL .
                        "5:" . $getVal('A/S 책임자와 전화번호 또는 소비자상담 관련 전화번호');
        }

        return [
            'infoDutyType' => $infoDutyType,
            'infoDuty' => $infoDuty
        ];
    }
}
