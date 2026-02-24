@extends('admin.layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title text-white"><i class="fas fa-images"></i> 일괄 이미지 업로드</h3>
            </div>
            
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="icon fas fa-exclamation-triangle"></i> 파일명에 주의해서 올려주시기 바랍니다.</h5>
                    <p class="mb-1">
                        - 파일명 형태는 반드시 <strong class="text-primary">상품코드_옵션</strong> (예: <code>11251_M3</code> -> 상품코드 11251 중 대표이미지 3번째 컷)으로 올려주십시오. <br>
                        - 이미지 형식은 오직 <strong>JPG</strong>만 가능하며, 파일 용량은 <strong>533KB 이하</strong>로 업로드 해야 합니다. <br>
                        - 리스트 이미지는 해상도 <strong>1000px X 1000px</strong> 크기로만 등록됩니다.<br>
                        - <strong class="text-danger">미노출 상품만 업데이트가 가능</strong>하며, 한번에 등록 가능한 이미지 개수는 최대 100장입니다.
                    </p>
                </div>

                <form id="image-form" method="POST" action="{{ route('admin.goods.img_uploads') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row mt-4">
                        <label class="col-sm-2 col-form-label">위 조건에 맞는 이미지 파일 첨부</label>
                        <div class="col-sm-10">
                            <div class="custom-file">
                                <input type="file" id="image-input" name="images[]" class="custom-file-input" multiple accept=".jpg,.jpeg">
                                <label class="custom-file-label" for="image-input" id="file-label">선택된 파일 없음 (최대 100개)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary" id="btn-upload"><i class="fas fa-upload"></i> 일괄 업로드 실행</button>
                    </div>
                </form>

                <hr>
                
                <div class="mt-4" id="preview-area" style="display:none;">
                    <h5>업로드 대기 중인 이미지 목록 (<span id="file-count">0</span>건)</h5>
                    <p class="text-muted text-sm">목록에서 이미지를 클릭하면 업로드 대상에서 제외됩니다.</p>
                    <div id="preview-container" class="d-flex flex-wrap border p-3 bg-light">
                        <!-- Previews injected here -->
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    const MAX_FILES = 100;
    let selectedFiles = []; // Array to hold actual files for submission

    function updateLabel() {
        $('#file-label').text(selectedFiles.length > 0 ? selectedFiles.length + '개 파일 선택됨' : '선택된 파일 없음 (최대 100개)');
        $('#file-count').text(selectedFiles.length);
        if(selectedFiles.length > 0) {
            $('#preview-area').show();
        } else {
            $('#preview-area').hide();
        }
    }

    // 미리보기 엘리먼트 렌더
    function createPreview(file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const wrapper = document.createElement('div');
            wrapper.className = 'm-2 border bg-white text-center shadow-sm position-relative';
            wrapper.style.width = '120px';
            wrapper.style.cursor = 'pointer';
            wrapper.title = '클릭하면 제외됩니다';
            
            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.objectFit = 'cover';
            img.style.width = '100%';
            img.style.height = '120px';
            
            const nameLabel = document.createElement('div');
            nameLabel.className = 'text-truncate px-1 py-1 small';
            nameLabel.textContent = file.name;

            wrapper.appendChild(img);
            wrapper.appendChild(nameLabel);
            
            // 제외 기능 (remove on click)
            wrapper.addEventListener('click', function() {
                const index = selectedFiles.indexOf(file);
                if (index !== -1) {
                    selectedFiles.splice(index, 1);
                }
                $(wrapper).fadeOut(200, function() {
                    $(this).remove();
                    updateLabel();
                });
                
                // Reset input completely if nothing is left to allow re-selection
                if(selectedFiles.length === 0) {
                    $('#image-input').val('');
                }
            });
            
            $('#preview-container').append(wrapper);
        };
        reader.readAsDataURL(file);
    }

    $('#image-input').on('change', function(event) {
        const files = event.target.files;
        
        // Append newly selected files
        for (let i = 0; i < files.length; i++) {
            if (selectedFiles.length < MAX_FILES) {
                // Check dupes by name
                if(!selectedFiles.find(f => f.name === files[i].name)) {
                    selectedFiles.push(files[i]);
                    createPreview(files[i]);
                }
            } else {
                alert('최대 100개까지만 선택 가능합니다. 초과분은 제외되었습니다.');
                break;
            }
        }
        
        updateLabel();
    });

    // 폼 제출 비동기 오버라이드
    $('#image-form').on('submit', function(event) {
        event.preventDefault();
        
        if(selectedFiles.length === 0) {
            alert('업로드할 파일을 찾지 못했습니다.');
            return;
        }

        if(!confirm('선택된 ' + selectedFiles.length + '개의 이미지를 일괄 업로드 처리하시겠습니까? (이 작업은 다소 시간이 소요될 수 있습니다.)')) {
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        
        for (let i = 0; i < selectedFiles.length; i++) {
            formData.append('images[]', selectedFiles[i]);
        }

        $('#btn-upload').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> 업로드 중...');

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                alert(res.message);
                if(res.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('서버 오류가 발생했습니다. (' + xhr.status + ')');
            },
            complete: function() {
                $('#btn-upload').prop('disabled', false).html('<i class="fas fa-upload"></i> 일괄 업로드 실행');
            }
        });
    });
</script>
@endsection
