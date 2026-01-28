/**
 * Quick Menu Actions (Cart/Buy) for Product Lists
 */
var QuickMenu = {
    csrfToken: '',

    init: function (token) {
        this.csrfToken = token;
    },

    /**
     * Add to Cart
     * @param {number} goodsSeq 
     * @param {number} optionSeq - Default option SEQ for single-option products
     * @param {boolean} hasMultipleOptions - If true, redirect to detail page
     */
    cart: function (goodsSeq, optionSeq, hasMultipleOptions) {
        if (hasMultipleOptions || !optionSeq) {
            if (confirm('옵션 선택이 필요한 상품입니다. 상품 상세페이지로 이동하시겠습니까?')) {
                location.href = '/goods/view?no=' + goodsSeq;
            }
            return;
        }

        // AJAX Add to Cart
        const formData = new FormData();
        formData.append('goods_seq', goodsSeq);
        formData.append('ea', 1);
        formData.append('option_seq', optionSeq);

        fetch('/order/cart/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (confirm('장바구니에 담겼습니다. 장바구니로 이동하시겠습니까?')) {
                        location.href = '/order/cart';
                    }
                } else {
                    alert(data.message || '장바구니 담기에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('오류가 발생했습니다.');
            });
    },

    /**
     * Buy Now
     * @param {number} goodsSeq 
     * @param {number} optionSeq 
     * @param {boolean} hasMultipleOptions 
     */
    buy: function (goodsSeq, optionSeq, hasMultipleOptions) {
        if (hasMultipleOptions || !optionSeq) {
            location.href = '/goods/view?no=' + goodsSeq;
            return;
        }

        // Add to Cart first, then redirect to Order Form
        const formData = new FormData();
        formData.append('goods_seq', goodsSeq);
        formData.append('ea', 1);
        formData.append('option_seq', optionSeq);

        fetch('/order/cart/add', { // Ensure correct route
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirect to Order Form with specific cart_seqs
                    let queryParams = '';
                    if (data.cart_seqs && Array.isArray(data.cart_seqs)) {
                        queryParams = data.cart_seqs.map(seq => `cart_seq[]=${seq}`).join('&');
                    }

                    if (queryParams) {
                        location.href = '/order/form?' + queryParams;
                    } else {
                        // Fallback implies something went wrong with capturing seq, but success was returned. 
                        // Just go to cart or show error.
                        alert('주문서 생성 중 오류가 발생했습니다.');
                    }
                } else {
                    alert(data.message || '구매 처리에 실패했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('오류가 발생했습니다.');
            });
    }
};
