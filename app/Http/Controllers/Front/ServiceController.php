<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function company()
    {
        return view('front.service.company');
    }

    private function getConfig($group, $code)
    {
        return \Illuminate\Support\Facades\DB::table('fm_config')
            ->where('groupcd', $group)
            ->where('codecd', $code)
            ->value('value');
    }

    private function getBasicConfig()
    {
        $configs = \Illuminate\Support\Facades\DB::table('fm_config')
            ->where('groupcd', 'basic')
            ->get();

        $arr = [];
        foreach ($configs as $row) {
            $arr[$row->codecd] = $row->value;
        }
        return $arr;
    }

    public function agreement()
    {
        $agreement = $this->getConfig('member', 'agreement');
        $basic = $this->getBasicConfig();

        $shopName = $basic['shopName'] ?? config('app.name');
        $agreement = str_replace("{shopName}", $shopName, $agreement);

        return view('front.service.agreement', compact('agreement'));
    }

    public function privacy()
    {
        $privacy = $this->getConfig('member', 'privacy');
        $basic = $this->getBasicConfig();

        $shopName = $basic['shopName'] ?? config('app.name');
        $domain = $basic['domain'] ?? request()->getHost();

        $privacy = str_replace("{shopName}", $shopName, $privacy);
        $privacy = str_replace("{domain}", $domain, $privacy);

        // Legacy replacements for privacy policy contact info
        // Simple mapping if keys exist in basic config
        $privacy = str_replace("{책임자명}", $basic['member_info_manager'] ?? '', $privacy);
        $privacy = str_replace("{책임자담당부서}", $basic['member_info_part'] ?? '', $privacy);
        $privacy = str_replace("{책임자직급}", $basic['member_info_rank'] ?? '', $privacy);
        $privacy = str_replace("{책임자연락처}", $basic['member_info_tel'] ?? '', $privacy);
        $privacy = str_replace("{책임자이메일}", $basic['member_info_email'] ?? '', $privacy);

        return view('front.service.privacy', compact('privacy'));
    }

    public function guide()
    {
        return view('front.service.guide');
    }

    public function partnership()
    {
        return view('front.service.partnership');
    }
}
