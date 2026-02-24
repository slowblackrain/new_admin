<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Goods;
use App\Models\GoodsImage;
use App\Models\ImgPassLog;

class ImageBatchController extends Controller
{
    // config('goodsImageSize') equivalent constants from legacy logic (or can be dynamic from config)
    private $imageSizes = [
        'large' => ['width' => 1000, 'height' => 1000],
        'view' => ['width' => 600, 'height' => 600],
        'list1' => ['width' => 300, 'height' => 300],
        'list2' => ['width' => 150, 'height' => 150],
        'thumbView' => ['width' => 75, 'height' => 75],
        'thumbCart' => ['width' => 50, 'height' => 50],
        'thumbScroll' => ['width' => 45, 'height' => 45],
    ];

    /**
     * Native GD helper to resize JPG images proportionally without cropping.
     */
    private function resizeImage($sourcePath, $targetPath, $maxWidth, $maxHeight)
    {
        list($origWidth, $origHeight) = getimagesize($sourcePath);

        // Aspect ratio calculation
        $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);

        // Do not upsize if original is smaller
        if ($ratio > 1) {
            $ratio = 1;
        }

        $newWidth = (int)($origWidth * $ratio);
        $newHeight = (int)($origHeight * $ratio);

        $imageP = imagecreatetruecolor($newWidth, $newHeight);
        $image = imagecreatefromjpeg($sourcePath);

        // Resize
        imagecopyresampled($imageP, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Output to file
        imagejpeg($imageP, $targetPath, 100);

        // Free up memory
        imagedestroy($imageP);
        imagedestroy($image);
    }

    public function popup_image_full()
    {
        return view('admin.goods.popup_image_full');
    }

    public function img_uploads(Request $request)
    {
        // 530kb limit as in legacy
        $limitSize_M = 0.53 * 1024 * 1024;
        
        $files = $request->file('images');
        if (!$files) {
            return response()->json(['success' => false, 'message' => '업로드할 파일이 없습니다.']);
        }

        $fileCount = count($files);
        if ($fileCount > 100) {
            return response()->json(['success' => false, 'message' => "100개 이상은 등록 불가합니다. 현재 {$fileCount}개 등록 시도 중입니다."]);
        }

        $passList = [];
        $successCount = 0;
        $managerId = auth()->guard('admin')->user()->manager_id ?? 'system';

        $goodsImageSizeConfig = config('goodsImageSize', $this->imageSizes);

        foreach ($files as $file) {
            $realName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $size = $file->getSize();

            if (!in_array($extension, ['jpg', 'png', 'gif'])) { // Although legacy says only jpg allowed later, initial check allows these
                $passList[$realName] = "지원하지 않는 확장자 입니다.";
                continue;
            }

            if ($extension !== 'jpg') {
                $passList[$realName] = "이미지 파일의 확장자는 jpg 여야 합니다. (레거시 스펙 일치화)";
                continue;
            }

            $rawName = pathinfo($realName, PATHINFO_FILENAME);
            $cleanName = trim(preg_replace("/([^a-zA-Z0-9_\-+])/", "", $rawName));

            $codeParts = explode('_', $cleanName);
            if (count($codeParts) < 2) {
                $passList[$realName] = "파일명 규약 위반 (코드_구분번호 형태 아님)";
                continue;
            }

            $goodsCodeOrScode = $codeParts[0];
            $typeAndIdx = $codeParts[1];
            
            $cType = substr($typeAndIdx, 0, 1);
            $idx = substr($typeAndIdx, 1);
            if (!is_numeric($idx)) $idx = 1;

            if ($cType === 'S') {
                $passList[$realName] = "상세 이미지는 아직 자동 등록 되지 않습니다.";
                continue;
            }

            // M type processing
            if ($cType !== 'M') {
                 $passList[$realName] = "M(대표이미지) 타입만 지원합니다.";
                 continue;
            }

            if ($size > $limitSize_M) {
                $passList[$realName] = "등록 상품의 이미지가 용량 제한(530KB)을 초과 하였습니다.";
                continue;
            }

            try {
                $imageSizeInfo = getimagesize($file->getPathname());
                if (!$imageSizeInfo || $imageSizeInfo[0] != 1000 || $imageSizeInfo[1] != 1000) {
                    $passList[$realName] = "리스트 이미지 등록 크기는 1000x1000 으로만 등록 가능합니다.";
                    continue;
                }
            } catch (\Exception $e) {
                $passList[$realName] = "이미지 크기 확인 실패: " . $e->getMessage();
                continue;
            }

            // Find strictly notLook product as requested
            $goods = Goods::where('goods_view', 'notLook')
                ->where(function($q) use ($goodsCodeOrScode) {
                    $q->where('goods_code', $goodsCodeOrScode)
                      ->orWhere('goods_scode', $goodsCodeOrScode);
                })
                ->first();

            if (!$goods) {
                $passList[$realName] = "매칭되는 상품이 없습니다.[노출상품은 일괄업로드에서 제외됨]";
                continue;
            }

            // Basic resizing and saving mimic (In Laravel, we'll store public disks or absolute paths depending on environment config)
            // Real resizing assumes GD or Imagick via Intervention
            $cpFolder = "data/goods/goods_img/1/" . date('Y/m') . "/" . $goods->goods_code . "/";
            $mkPath = public_path($cpFolder);
            if (!File::exists($mkPath)) {
                File::makeDirectory($mkPath, 0777, true, true);
            }

            try {
                $imagesToSave = [];
                foreach ($goodsImageSizeConfig as $type => $dimensions) {
                    $newName = $idx . "_{$type}." . $extension; // Mocking legacy naming convention or similar
                    
                    // Legacy saves as `{idx}_xxxxxthumbScroll.JPG` using uniqid inside `goods_temp_image_resize` normally.
                    // For simplicity, we just save them distinctively
                    $destinationPath = $mkPath . $idx . "_" . time() . "_{$type}." . $extension;
                    $relativePath = "/" . $cpFolder . $idx . "_" . time() . "_{$type}." . $extension;
                    
                    // Simple resize using Intervention
                    // Simple resize using native PHP GD helper
                    if (function_exists('imagecreatetruecolor')) {
                        $this->resizeImage($file->getRealPath(), $destinationPath, $dimensions['width'], $dimensions['height']);
                    } else {
                        File::copy($file->getRealPath(), $destinationPath);
                    }

                    $imagesToSave[$type] = $relativePath;
                }

                if ($idx == 1 || $idx == '1' || $idx == '') {
                    // Update main record log if it's main cut 1
                    $adminLogs = "<div>" . date('Y-m-d H:i:s') . " 관리자($managerId)가 상품의 이미지를 일괄변경하였습니다. ({$request->ip()}) - {$realName}</div>" . $goods->admin_log;
                    $goods->admin_log = $adminLogs;
                    $goods->update_date = date('Y-m-d H:i:s');
                    $goods->save();
                    
                    // Replace existing cut index
                    GoodsImage::where('goods_seq', $goods->goods_seq)->where('cut_number', $idx)->delete();
                }

                foreach ($imagesToSave as $imgType => $imgPath) {
                    GoodsImage::create([
                        'goods_seq' => $goods->goods_seq,
                        'cut_number' => $idx,
                        'image_type' => $imgType,
                        'image' => $imgPath,
                        'match_color' => ''
                    ]);
                }

                $successCount++;

            } catch (\Exception $ex) {
                $passList[$realName] = "리사이징 또는 업로드 처리 중 오류 발생: " . $ex->getMessage();
            }
        }

        $passMsg = "";
        if (count($passList) > 0) {
            ImgPassLog::create([
                'manager_id' => $managerId,
                'reg_date' => date('Y-m-d H:i:s'),
                'miss_msg' => "{$fileCount}중에 " . count($passList) . " 건이 업데이트 누락 되었습니다.",
                'log_msg' => json_encode($passList, JSON_UNESCAPED_UNICODE)
            ]);
            
            foreach ($passList as $key => $err) {
                $passMsg .= "\n[$key] => $err";
            }
        }

        if ($fileCount > 0) {
            $finalMsg = "등록 처리가 완료되었습니다. [ {$successCount} / {$fileCount} ] 등록됨." . ($passMsg ? "\n\n오류 내역 (fm_img_pass_log 참조):" . $passMsg : "");
            return response()->json(['success' => true, 'message' => $finalMsg]);
        }

        return response()->json(['success' => false, 'message' => '등록할 이미지가 처리되지 않았습니다.']);
    }
}
