<?php

return [
    // [재고기초] scmbasic
    'scmbasic_group' => [
        'name' => '재고기초',
        'items' => [
            ['name' => '재고기초', 'url' => '/admin/scm_basic/config'],
            ['name' => '절사/환율, 기초일자', 'url' => '/admin/scm_basic/config'],
            ['name' => '거래처', 'url' => '/admin/scm_basic/trader'],
            ['name' => '창고', 'url' => '/admin/scm_basic/warehouse'],
            ['name' => '쇼핑몰 창고', 'url' => '/admin/scm_basic/store'],
            // ['name' => '기본단가요율설정', 'url' => '/admin/scm_basic/goods_int_set'], // TODO: Apply Access Control (Legacy: dmtadmin only) - Unlock later
        ]
    ],
    // [재고관리] scmmanage
    'scmmanage_group' => [
        'name' => '재고관리',
        'items' => [
            ['name' => '재고조정', 'url' => '/admin/scm_manage/revision'],
        ]
    ],
    // [발주/입고] scmwarehousing
    'scmwarehousing_group' => [
        'name' => '발주/입고',
        'items' => [
            ['name' => '발주/입고', 'url' => '/admin/scm_doto_warehousing/catalog'],
            ['name' => '일반 주문리스트', 'url' => '/admin/scm_doto_warehousing/catalog'],
            ['name' => '온라인 주문리스트', 'url' => '/admin/scm_doto_warehousing/catalog_on'], // Placeholder
            ['name' => '한국 주문리스트', 'url' => '/admin/scm_doto_warehousing/catalog_kr'], // Placeholder
            ['name' => '직구리스트', 'url' => '/admin/scm_doto_warehousing/catalog_direct_buy'], // Placeholder
            ['name' => '중국정산관리', 'url' => '/admin/scm_doto_warehousing/calculate_calendar'],
            ['name' => '한국정산관리', 'url' => '/admin/scm_doto_warehousing/catalog_receipt'],
            ['name' => '입고', 'url' => '/admin/scm_warehousing/warehousing'],
            ['name' => '반출', 'url' => '/admin/scm_warehousing/carryingout'], // Placeholder
        ]
    ],
    // [판매상품] goods
    'goods_group' => [
        'name' => '판매상품',
        'items' => [
            ['name' => '판매상품', 'url' => '/admin/goods/catalog'],
            ['name' => '<실물> 상품', 'url' => '/admin/goods/catalog'],
            ['name' => '세트 상품', 'url' => '/admin/goods/goods_set'],
            ['name' => '일괄 업데이트', 'url' => '/admin/goods/batch_modify'],
            ['name' => '카테고리', 'url' => '/admin/category/catalog'],
            ['name' => '지역', 'url' => '/admin/location/catalog'],
            ['name' => '진열번호관리', 'url' => '/admin/goods/sortcd_catalog'], // Placeholder
            ['name' => '외부연동', 'url' => '/admin/goods/out_catalog'],
            ['name' => '일괄이미지등록', 'url' => '/admin/goods/popup_image_full'],
            // New but essential for parity access
            ['name' => '상품 등록', 'url' => '/admin/goods/regist'], 
        ]
    ],
    // [주문] order
    'order_group' => [
        'name' => '주문',
        'items' => [
            ['name' => '주문', 'url' => '/admin/order/index'], // Placeholder
            ['name' => '주문리스트', 'url' => '/admin/order/catalog'],
            ['name' => '송장등록리스트', 'url' => '/admin/goods/deli_catalog'],
            ['name' => '반품리스트', 'url' => '/admin/returns/catalog'],
            ['name' => '환불리스트', 'url' => '/admin/refund/catalog'],
            ['name' => '매출증빙리스트', 'url' => '/admin/order/sales'],
            ['name' => '상담메모관리', 'url' => '/admin/order/customer_memo'],
            ['name' => '송장엑셀처리', 'url' => '/admin/order/excel_songjang'],
            ['name' => '무통장입금확인', 'url' => '/admin/order/math_bank'],
            ['name' => '외부주문수집', 'url' => '/admin/goods/out_orders'],
        ]
    ],
    // [회원] member
    'member_group' => [
        'name' => '회원',
        'items' => [
            ['name' => '회원', 'url' => '/admin/member/index'], // Placeholder
            ['name' => '회원리스트', 'url' => '/admin/member/catalog'],
            ['name' => '회원매출관리', 'url' => '/admin/member/member_sale_catalog'],
            ['name' => '매니저매출관리', 'url' => '/admin/member/manager_sale_catalog'], // Placeholder
            ['name' => '셀러프로모션관리', 'url' => '/admin/member/seller_promotion'], // Placeholder
            ['name' => '휴면처리리스트', 'url' => '/admin/member/dormancy_catalog'],
            ['name' => '탈퇴리스트', 'url' => '/admin/member/withdrawal'], // Placeholder
            ['name' => '특별작업', 'url' => '/admin/exe/goods_price_history'], // Placeholder
            ['name' => '스마일알림톡', 'url' => '/admin/member/s_kakao_log'],
            ['name' => '스마일SMS', 'url' => '/admin/member/s_sms_log'], // Placeholder
            ['name' => '이메일발송관리', 'url' => '/admin/member/email'],
        ]
    ],
    // [게시판] board
    'board_group' => [
        'name' => '게시판',
        'items' => [
            ['name' => '게시판', 'url' => '/admin/board/index'],
            ['name' => '게시글 리스트', 'url' => '/admin/board/index'],
            ['name' => '게시판 리스트', 'url' => '/admin/board/main'], // Placeholder
            ['name' => '고객상담 통합게시판', 'url' => '/admin/board/counsel_catalog'],
        ]
    ],
    // [프로모션/쿠폰] coupon
    'coupon_group' => [
        'name' => '프로모션/쿠폰',
        'items' => [
            ['name' => '할인 쿠폰', 'url' => '/admin/coupon/catalog'],
            ['name' => '할인 이벤트', 'url' => '/admin/event/catalog'],
        ]
    ],
    // [마케팅] marketing
    'marketing_group' => [
        'name' => '마케팅',
        'items' => [
            ['name' => '마케팅', 'url' => '/admin/marketing/index'], // Placeholder
            ['name' => '광고현황', 'url' => '/admin/marketing/ad_status'],
            ['name' => '방문자현황', 'url' => '/admin/marketing/cpc_cker'], // Placeholder
        ]
    ],
    // [통계] statistic
    'statistic_group' => [
        'name' => '통계',
        'items' => [
            ['name' => '통계', 'url' => '/admin/statistic_summary'],
            ['name' => '발주용통계', 'url' => '/admin/statistic_doto'],
            ['name' => '코드별통계', 'url' => '/admin/statistic_doto/sales_code2'], // Adjusted URL
            ['name' => '카테고리매출통계', 'url' => '/admin/statistic_doto/sales_category'],
            ['name' => '재고수불부', 'url' => '/admin/statistic_doto/subul'],
            ['name' => '직원 구매내역', 'url' => '/admin/statistic_omem/sales_mem'], // Placeholder
            ['name' => '요약 통계', 'url' => '/admin/statistic_summary'],
            ['name' => '방문 통계', 'url' => '/admin/statistic_visitor'],
            ['name' => '가입 통계', 'url' => '/admin/statistic_member'], // Placeholder
        ]
    ],
    // [입점사] provider
    'provider_group' => [
        'name' => '입점사',
        'items' => [
            ['name' => '입점사', 'url' => '/admin/provider/provider'],
            ['name' => '입점사리스트', 'url' => '/admin/provider/provider'],
        ]
    ],
    // [정산] account
    'account_group' => [
        'name' => '정산',
        'items' => [
            ['name' => '정산', 'url' => '/admin/account/index'],
            ['name' => '마이너스캐시리스트', 'url' => '/admin/account_provider/member_fail_cash'],
            ['name' => '판매대행정산리스트', 'url' => '/admin/statistic_sales/sales_ATS_seller'], // Placeholder
            ['name' => '유리스정산페이지', 'url' => '/admin/member/youis'], // Placeholder
        ]
    ],
    // [설정] setting
    'setting_group' => [
        'name' => '설정',
        'items' => [
            ['name' => '기본 환경 설정', 'url' => '/admin/setting/index'],
            ['name' => '상점 일반 정보', 'url' => '/admin/setting/basic'],
            ['name' => '운영/판매 정책', 'url' => '/admin/setting/operating'],
            ['name' => '결제/배송 설정', 'url' => '/admin/setting/pg'],
            ['name' => '회원/적립금 설정', 'url' => '/admin/setting/member'],
            ['name' => '보안/관리자 설정', 'url' => '/admin/setting/protect'],
        ]
    ],
    // [SCM Beta] New Features
    'scm_beta_group' => [
        'name' => 'SCM (Beta)',
        'items' => [
            ['name' => '자동 발주 관리 (New)', 'url' => '/admin/scm_order/auto'],
            ['name' => '발주 현황 (New)', 'url' => '/admin/scm_order/list'],
            ['name' => '재고 수불부 (New)', 'url' => '/admin/scm_manage/ledger'],
            ['name' => '월별 매입 정산 (New)', 'url' => '/admin/scm_settlement/trader_monthly'],
        ]
    ],
];
