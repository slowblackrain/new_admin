@extends('admin.layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">카테고리 관리</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left: Tree View -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline h-100">
                        <div class="card-header">
                            <h3 class="card-title">카테고리 구조</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-success" onclick="createNode()">
                                    <i class="fas fa-plus"></i> 추가
                                </button>
                                <button type="button" class="btn btn-sm btn-info" onclick="refreshTree()">
                                    <i class="fas fa-sync"></i> 새로고침
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="overflow-y:auto; max-height: 800px;">
                            <div id="categoryTree"></div> <!-- JSTree Target -->
                        </div>
                    </div>
                </div>

                <!-- Right: Settings Form -->
                <div class="col-md-8">
                    <div class="card card-secondary h-100">
                        <div class="card-header">
                            <h3 class="card-title">상세 설정</h3>
                        </div>
                        <div class="card-body">
                            <form id="categoryForm">
                                <input type="hidden" name="id" id="cat_id">
                                <input type="hidden" name="parent_id" id="cat_parent_id">
                                
                                <div class="form-group">
                                    <label>카테고리명</label>
                                    <input type="text" name="title" id="cat_title" class="form-control" placeholder="카테고리를 선택하세요">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>카테고리 코드</label>
                                            <input type="text" name="category_code" id="cat_code" class="form-control" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>상품 수</label>
                                            <input type="text" id="cat_goods_count" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="cat_hide" name="hide" value="0">
                                        <label class="custom-control-label" for="cat_hide">쇼핑몰 노출 (OFF시 숨김)</label>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="form-group">
                                    <label>연결된 상품 (최근 50개)</label>
                                    <table class="table table-sm table-bordered table-striped" id="goodsTable">
                                        <thead>
                                            <tr>
                                                <th>순서</th>
                                                <th>상품코드</th>
                                                <th>상품명</th>
                                                <th>판매가</th>
                                                <th>상태</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- AJAX Content -->
                                        </tbody>
                                    </table>
                                </div>

                                <hr>
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary" onclick="saveCategory()" id="btnSave" disabled>저장</button>
                                    <button type="button" class="btn btn-danger" onclick="deleteCategory()" id="btnDelete" disabled>삭제</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JSTree CSS/JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

<script>
$(document).ready(function(){
    initTree();
});

function initTree() {
    $('#categoryTree').jstree({
        'core' : {
            'data' : {
                'url' : "{{ route('admin.category.tree') }}",
                'data' : function (node) {
                    return { 'id' : node.id };
                }
            },
            'check_callback' : true, // Enable CRUD
            'themes' : {
                'responsive': false
            }
        },
        'plugins' : ['dnd', 'types', 'state']
    }).on('select_node.jstree', function (e, data) {
        loadDetail(data.node.id);
    }).on('move_node.jstree', function (e, data) {
        // Data: node, parent, position, old_parent, old_position
        $.ajax({
            url: "{{ route('admin.category.move') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: data.node.id,
                parent_id: data.parent,
                position: data.position
            },
            success: function(res) {
                console.log('Moved');
            },
            error: function(err) {
                alert('이동 실패: ' + err.responseJSON.error);
                data.instance.refresh(); // Revert
            }
        });
    });
}

function refreshTree() {
    $('#categoryTree').jstree(true).refresh();
}

function loadDetail(id) {
    if(!id || id === '#') return;
    
    // Ajax load
    $.ajax({
        url: "/admin/category/detail/" + id,
        method: "GET",
        success: function(res) {
            $('#cat_id').val(res.id);
            $('#cat_parent_id').val(res.parent_id);
            $('#cat_title').val(res.title);
            $('#cat_code').val(res.category_code);
            $('#cat_goods_count').val(res.goods_count);
            
            // Toggle Switch: hide column: 1=Hidden(True), 0=Visible(False)
            // If hide==0, Checked (Visible). If hide==1, Unchecked (Hidden).
            $('#cat_hide').prop('checked', res.hide == '0');
            
            $('#btnSave').prop('disabled', false);
            $('#btnDelete').prop('disabled', false);
            
            // Load Goods
            loadGoods(id);
        },
        error: function(err) {
            console.error(err);
        }
    });
}

function loadGoods(id) {
    let tbody = $('#goodsTable tbody');
    tbody.html('<tr><td colspan="5" class="text-center">로딩중...</td></tr>');
    
    $.ajax({
        url: "/admin/category/goods/" + id,
        method: "GET",
        success: function(res) {
            tbody.empty();
            if(res.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center">등록된 상품이 없습니다.</td></tr>');
                return;
            }
            
            res.forEach(function(g) {
                tbody.append(`
                    <tr>
                        <td>${g.sort}</td>
                        <td>${g.goods_code}</td>
                        <td>${g.goods_name}</td>
                        <td>${Number(g.sale_price).toLocaleString()}</td>
                        <td>${g.goods_status}</td>
                    </tr>
                `);
            });
        },
        error: function() {
             tbody.html('<tr><td colspan="5" class="text-center text-danger">로딩 실패</td></tr>');
        }
    });
}

function createNode() {
    let ref = $('#categoryTree').jstree(true);
    let sel = ref.get_selected();
    let parent_id = sel.length ? sel[0] : 1; // Default to Root(1) if none selected
    
    if(parent_id === '#') parent_id = 1;

    $.ajax({
        url: "{{ route('admin.category.store') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            parent_id: parent_id
        },
        success: function(res) {
            refreshTree(); // Refresh to show new node
            // Optional: Open parent and select new node
        },
        error: function(err) {
            alert('생성 실패');
        }
    });
}

function saveCategory() {
    let id = $('#cat_id').val();
    if(!id) return;

    let title = $('#cat_title').val();
    let hide = $('#cat_hide').is(':checked') ? '0' : '1'; // Checked=Show=0, Unchecked=Hide=1

    $.ajax({
        url: "/admin/category/update/" + id,
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            title: title,
            hide: hide
        },
        success: function(res) {
            alert('저장되었습니다.');
            refreshTree(); // To update title in tree
        },
        error: function(err) {
            alert('저장 실패');
        }
    });
}

function deleteCategory() {
    let id = $('#cat_id').val();
    if(!id) return;
    
    if(!confirm('정말 삭제하시겠습니까?')) return;
    
    $.ajax({
        url: "/admin/category/destroy/" + id,
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function(res) {
            alert('삭제되었습니다.');
            refreshTree();
            // Reset form
            $('#categoryForm')[0].reset();
            $('#btnSave').prop('disabled', true);
            $('#btnDelete').prop('disabled', true);
        },
        error: function(xhr) {
            alert('삭제 실패: ' + (xhr.responseJSON.error || '오류 발생'));
        }
    });
}
</script>
@endsection
