@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    Brand Tree
                    <button class="btn btn-sm btn-primary float-end" onclick="createRootNode()">+ Root</button>
                </div>
                <div class="card-body">
                    <div id="tree"></div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">Brand Details</div>
                <div class="card-body" id="categorySettingContainer">
                    <p class="text-muted text-center">Select a brand from the tree to edit.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>

<script>
$(function () {
    $('#tree').jstree({
        'core' : {
            'data' : {
                'url' : "{{ route('admin.goods.brand.tree') }}",
                'data' : function (node) {
                    return { 'id' : node.id, 'operation': 'get_children' };
                }
            },
            'check_callback' : true,
            'themes' : {
                'responsive': false
            }
        },
        'plugins' : ['state','dnd','contextmenu','types', 'wholerow']
    }).on('create_node.jstree', function (e, data) {
        $.post("{{ route('admin.goods.brand.tree') }}", { 
            "operation" : "create_node", 
            "id" : data.node.parent, 
            "position" : data.position,
            "title" : data.node.text,
            "_token": "{{ csrf_token() }}"
        })
        .done(function (d) {
            data.instance.set_id(data.node, d.id);
            // Reload to get correct code
        })
        .fail(function () {
            data.instance.refresh();
        });
    }).on('rename_node.jstree', function (e, data) {
        $.post("{{ route('admin.goods.brand.tree') }}", { 
            "operation" : "rename_node", 
            "id" : data.node.id, 
            "title" : data.text,
            "_token": "{{ csrf_token() }}"
        })
        .fail(function () {
            data.instance.refresh();
        });
    }).on('delete_node.jstree', function (e, data) {
        $.post("{{ route('admin.goods.brand.tree') }}", { 
            "operation" : "remove_node", 
            "id" : data.node.id,
            "_token": "{{ csrf_token() }}"
        })
        .fail(function () {
            data.instance.refresh();
        });
    }).on('select_node.jstree', function (e, data) {
        var categoryCode = data.node.li_attr ? data.node.li_attr.category : null;
        if(categoryCode) {
            $("#categorySettingContainer").html('<div class="text-center p-5"><div class="spinner-border" role="status"></div></div>');
            $.get("{{ url('admin/goods/brand/show') }}/" + categoryCode)
            .done(function(html) {
                $("#categorySettingContainer").html(html);
            })
            .fail(function() {
                $("#categorySettingContainer").html('<p class="text-danger">Error loading brand details.</p>');
            });
        }
    });
});
function createRootNode() {
    $('#tree').jstree('create_node', '#', { 'text': 'New Brand', 'type': 'folder' }, 'last');
}
</script>
@endsection
