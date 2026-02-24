<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Member;

class MemberManagementService
{
    /**
     * Process member withdrawal
     * 1. Logs withdrawal sequence and reason to fm_member_withdrawal
     * 2. Scrubs PII from fm_member
     * 3. Changes status to 'withdrawal'
     * 4. Deletes related records from SNS, Business, Delivery Address, and DR tables
     *
     * @param int $memberSeq
     * @param string $reason
     * @param string $actor 'user' or 'admin'
     * @return bool
     * @throws \Exception
     */
    public function processWithdrawal($memberSeq, $reason, $actor = 'user')
    {
        $member = Member::find($memberSeq);
        if (!$member) {
            throw new \Exception("Member not found.");
        }

        if ($member->status === 'withdrawal') {
            throw new \Exception("Member is already withdrawn.");
        }

        DB::beginTransaction();

        try {
            // 1. Log to fm_member_withdrawal
            // Legacy schema: member_seq, reason, memo, regist_ip, regist_date
            DB::table('fm_member_withdrawal')->insert([
                'member_seq' => $memberSeq,
                'reason' => $reason,
                'memo' => 'Processed by Route: ' . $actor,
                'regist_ip' => request()->ip(),
                'regist_date' => now(),
            ]);

            // 2 & 3. Scrub PII and update status
            // Note: Legacy changes userid to 'withdrawal_'.$userid if SNS, but we'll blank most fields.
            $updateData = [
                'password' => '',
                'user_name' => '',
                'gubun_seq' => 0,
                'group_seq' => 1, // Change to general group
                'email' => '',
                'phone' => '',
                'cellphone' => '',
                'zipcode' => '',
                'address_type' => '',
                'address' => '',
                'address_street' => '',
                'address_detail' => '',
                'sex' => 'none',
                'birthday' => '',
                'company' => '',
                'auth_code' => '',
                'auth_vno' => '',
                'auth_type' => '',
                'status' => 'withdrawal',
            ];

            // If SNS Member, legacy adds 'withdrawal_' prefix to userid
            $snsRecord = DB::table('fm_membersns')->where('member_seq', $memberSeq)->first();
            if ($snsRecord) {
                // If Naver (sns_n == userid), it swaps userid to conv_sns_n. For simplicity, just prefix.
                $updateData['userid'] = 'withdrawal_' . $member->userid;
            }

            // Wipe SNS columns from main table if they exist
            $snsColumns = ['sns_f', 'sns_t', 'sns_g', 'sns_m', 'sns_k', 'sns_n'];
            foreach ($snsColumns as $col) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('fm_member', $col)) {
                    $updateData[$col] = '';
                }
            }

            DB::table('fm_member')->where('member_seq', $memberSeq)->update($updateData);

            // 4. Delete related records
            DB::table('fm_member_business')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_membersns')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_delivery_address')->where('member_seq', $memberSeq)->delete();

            // Delete from DR (Dormant) backups if they exist
            DB::table('fm_member_dr')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_member_business_dr')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_membersns_dr')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_delivery_address_dr')->where('member_seq', $memberSeq)->delete();

            DB::commit();

            Log::info("Member withdrawal processed.", ['member_seq' => $memberSeq, 'actor' => $actor]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process withdrawal: " . $e->getMessage(), ['member_seq' => $memberSeq, 'actor' => $actor]);
            throw $e;
        }
    }

    /**
     * Set a member as Dormant (Sleep)
     * Moves PII to DR tables and scrubs fm_member
     *
     * @param int $memberSeq
     * @return int dormancy_seq
     * @throws \Exception
     */
    public function processDormancyOn($memberSeq)
    {
        $member = Member::find($memberSeq);
        if (!$member || $member->status === 'withdrawal') {
            throw new \Exception("Invalid member for dormancy.");
        }
        if ($member->status === 'dormancy' || !empty($member->dormancy_seq)) {
            throw new \Exception("Member is already dormant.");
        }

        DB::beginTransaction();
        try {
            // 1. Create Log Entry
            $logId = DB::table('fm_dormancy_log')->insertGetId([
                'member_seq' => $memberSeq,
                'log_type' => 'on',
                'log_date' => now(),
            ]);

            // Helper closure to filter keys based on destination table columns
            $insertMatchCols = function($tableName, $dataArray) {
                // Remove potential pk keys if they cause conflicts or just let it insert them depending on schema
                // Actually safer to query columns
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
                return array_intersect_key($dataArray, array_flip($columns));
            };

            // fm_member_dr
            $memberData = $member->toArray();
            $memberData['dormancy_date'] = now();
            unset($memberData['dormancy_seq']);
            DB::table('fm_member_dr')->insert($insertMatchCols('fm_member_dr', $memberData));

            // fm_membersns_dr
            $snsRecords = DB::table('fm_membersns')->where('member_seq', $memberSeq)->get();
            foreach ($snsRecords as $sns) {
                DB::table('fm_membersns_dr')->insert($insertMatchCols('fm_membersns_dr', (array)$sns));
            }

            // fm_member_business_dr
            $bizRecords = DB::table('fm_member_business')->where('member_seq', $memberSeq)->get();
            foreach ($bizRecords as $biz) {
                DB::table('fm_member_business_dr')->insert($insertMatchCols('fm_member_business_dr', (array)$biz));
            }

            // fm_delivery_address_dr
            $delRecords = DB::table('fm_delivery_address')->where('member_seq', $memberSeq)->get();
            foreach ($delRecords as $del) {
                DB::table('fm_delivery_address_dr')->insert($insertMatchCols('fm_delivery_address_dr', (array)$del));
            }

            // 3. Scrub PII from Main Tables
            $memberUpdate = [
                'dormancy_seq' => $logId,
                'status' => 'dormancy',
                'group_seq' => 1, // Fallback to basic group
                'user_name' => '',
                'email' => '',
                'phone' => '',
                'cellphone' => '',
                'zipcode' => '',
                'address_type' => '',
                'address' => '',
                'address_street' => '',
                'address_detail' => '',
                'birthday' => '',
                'auth_code' => '',
                'auth_vno' => '',
                'auth_type' => '',
            ];
            DB::table('fm_member')->where('member_seq', $memberSeq)->update($memberUpdate);

            // Scrub SNS
            $snsScrub = [
                // Typically sns tokens
                'sns_id' => '', // Check exact column names from schema
            ];
            // Get actual columns to scrub properly, or just delete. Legacy scrubs, let's just delete to be safe, or leave seq
            // Based on legacy logic, it updates columns to '' but leaves relations. Let's just update all string fields to blank.
            // Simplified:
            DB::table('fm_membersns')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_member_business')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_delivery_address')->where('member_seq', $memberSeq)->delete();


            DB::commit();
            return $logId;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to turn on dormancy: " . $e->getMessage(), ['member_seq' => $memberSeq]);
            throw $e;
        }
    }

    /**
     * Restore a member from Dormant State
     *
     * @param int $memberSeq
     * @return int dormancy_seq (log id)
     * @throws \Exception
     */
    public function processDormancyOff($memberSeq)
    {
        $member = Member::find($memberSeq);
        if (!$member || $member->status !== 'dormancy') {
            throw new \Exception("Member is not dormant.");
        }

        DB::beginTransaction();

        try {
            // 1. Create Log Entry
            $logId = DB::table('fm_dormancy_log')->insertGetId([
                'member_seq' => $memberSeq,
                'log_type' => 'off',
                'log_date' => now(),
            ]);

            // 2. Fetch from DR
            $drMember = DB::table('fm_member_dr')->where('member_seq', $memberSeq)->first();
            if (!$drMember) {
                throw new \Exception("Dormant backup record not found.");
            }

            // 3. Restore to Main Table
            $restoreData = [
                'user_name' => $drMember->user_name,
                'email' => $drMember->email,
                'phone' => $drMember->phone,
                'cellphone' => $drMember->cellphone,
                'zipcode' => $drMember->zipcode,
                'address_type' => $drMember->address_type,
                'address' => $drMember->address,
                'address_street' => $drMember->address_street,
                'address_detail' => $drMember->address_detail,
                'birthday' => $drMember->birthday,
                'auth_code' => $drMember->auth_code,
                'auth_vno' => $drMember->auth_vno,
                'auth_type' => $drMember->auth_type,
                'status' => $drMember->status, // Restore original status
                'dormancy_seq' => null, // Clear seq
                'lastlogin_date' => now(), // Update last login to prevent immediate re-休眠
            ];

            DB::table('fm_member')->where('member_seq', $memberSeq)->update($restoreData);

            // Helper closure to filter keys based on destination table columns
            $insertMatchCols = function($tableName, $dataArray) {
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing($tableName);
                return array_intersect_key($dataArray, array_flip($columns));
            };

            // Restore SNS
            $drSnsRecords = DB::table('fm_membersns_dr')->where('member_seq', $memberSeq)->get();
            foreach($drSnsRecords as $s) {
                $sData = (array)$s;
                unset($sData['seq']); // Avoid PK conflict
                DB::table('fm_membersns')->insert($insertMatchCols('fm_membersns', $sData));
            }

            // Restore Business
            $drBizRecords = DB::table('fm_member_business_dr')->where('member_seq', $memberSeq)->get();
            foreach($drBizRecords as $b) {
                $bData = (array)$b;
                unset($bData['business_seq']); // Avoid PK conflict if auto-increment
                DB::table('fm_member_business')->insert($insertMatchCols('fm_member_business', $bData));
            }

            // Restore Delivery Address
            $drDelRecords = DB::table('fm_delivery_address_dr')->where('member_seq', $memberSeq)->get();
            foreach($drDelRecords as $d) {
                $dData = (array)$d;
                unset($dData['address_seq']); // Avoid PK conflict
                DB::table('fm_delivery_address')->insert($insertMatchCols('fm_delivery_address', $dData));
            }

            // 4. Delete from DR
            DB::table('fm_member_dr')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_membersns_dr')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_member_business_dr')->where('member_seq', $memberSeq)->delete();
            DB::table('fm_delivery_address_dr')->where('member_seq', $memberSeq)->delete();

            DB::commit();
            return $logId;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to turn off dormancy: " . $e->getMessage(), ['member_seq' => $memberSeq]);
            throw $e;
        }
    }
}
