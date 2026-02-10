<?php
namespace App\Libraries;

// Using vanilla require as instructed for legacy support
// Assuming nusoap.php is patched for PHP 8
require_once __DIR__ . '/nusoap_polyfill.php';
if (!class_exists('nusoap_client')) {
    require_once __DIR__ . '/nusoap.php';
}

class Hiworks_Bill {

    const DOCUMENTTYPE_TAX = 'A';
    const DOCUMENTTYPE_BILL = 'B';
    const TAXTYPE_TAX = 'A';
    const TAXTYPE_NOTAX = 'B';
    const TAXTYPE_MANUAL = 'D'; // Not used?
    const SENDTYPE_SEND = 'S';
    const SENDTYPE_RECV = 'R';
    const PTYPE_RECEIPT = 'R';
    const PTYPE_CALL = 'C';
    const COMPANYPREFIX_SUPPLIER = 's';
    const COMPANYPREFIX_CONSUMER = 'r';
    const SOAPSERVER_URL = 'http://billapi.hiworks.co.kr/server.php?wsdl';

    public $client = null;
    public $document_id = null;
    public $error = array();

    public $builtin_array = array();
    public $basic_array = array();
    public $document_array = array();

    public $supply_array = array();
    public $work_array = array();

    public $check_document_array = array();

    public $sum_price = 0;
    public $sum_tax = 0;

    public function __construct( $domain, $license_id, $license_no, $partner_id )
    {   
        if (!$this->is($domain)||!$this->is($license_id)||!$this->is($license_no)||!$this->is($partner_id)) {
            die('Hiworks_Bill Config Not Found!!');
        }

        $this->builtin_array['domain'] = $domain;
        $this->builtin_array['license_id'] = $license_id;
        $this->builtin_array['license_no'] = $license_no;
        $this->builtin_array['partner_id'] = $partner_id;
    }

    public function set_type( $type='A', $kind='A', $sendtype='S' )
    {
        $this->basic_array['d_type'] = (in_array(strtoupper($type), array('A', 'B'))) ? $type : 'A';
        $this->basic_array['kind'] = (in_array(strtoupper($kind), array('A', 'B', 'D'))) ? $kind : 'A';
        $this->basic_array['sendtype'] = (in_array(strtoupper($sendtype), array('S', 'R'))) ? $sendtype : 'S';

        if( $this->basic_array['d_type'] == 'B' ) {
            $this->basic_array['kind'] = 'B';
        }

        return true;
    }

    public function set_basic_info( $name, $email, $hp='', $memo='', $book_no='', $serial='' )
    {
        if (!$this->is($name)||!$this->is($email)) {
            return false;
        }
        $this->basic_array['c_name'] = $name;
        $this->basic_array['c_email'] = $email;
        $this->basic_array['c_cell'] = $hp;
        $this->basic_array['memo'] = $memo;
        $this->basic_array['book_no'] = $book_no;
        $this->basic_array['serial'] = $serial;

        return true;
    }

    public function set_company_info( $number, $name, $master, $address, $condition, $item, $prefix='s' )
    {
        if (!$this->is($number)||!$this->is($name)||!$this->is($master)||!$this->is($address)||!$this->is($condition)||!$this->is($item)) {
            return false;
        }

        $pre = (in_array(strtolower($prefix), array('s', 'r'))) ? $prefix : 's';
        $key = ($pre=='s') ? 0 : 1;
        $this->supply_array[$key][$pre.'_number'] = $number;
        $this->supply_array[$key][$pre.'_name'] = $name;
        $this->supply_array[$key][$pre.'_master'] = $master;
        $this->supply_array[$key][$pre.'_address'] = $address;
        $this->supply_array[$key][$pre.'_condition'] = $condition;
        $this->supply_array[$key][$pre.'_item'] = $item;

        return true;
    }

    public function set_document_info( $issue_date, $supplyprice, $tax, $ptype='R', $remark='', $money='', $moneycheck='', $bill='', $uncollect='' )
    {
        if (!$this->is($issue_date)||!$this->is($supplyprice)||!$this->is($tax)) {
            return false;
        }

        $this->document_array['issue_date'] = $issue_date;
        $this->document_array['supplyprice'] = $this->cleaner($supplyprice);
        $this->document_array['tax'] = $this->cleaner($tax);
        $this->document_array['p_type'] = (in_array(strtoupper($ptype), array('R', 'C'))) ? $ptype : 'R';
        $this->document_array['remark'] = $remark;
        $this->document_array['money'] = $money;
        $this->document_array['moneycheck'] = $moneycheck;
        $this->document_array['bill'] = $bill;
        $this->document_array['uncollect'] = $uncollect;

        return true;
    }

    public function set_work_info( $mm, $dd, $subject, $form, $count, $oneprice, $price=0, $tax_row=0, $etc='', $sum=0 )
    {
        if (!$this->is($mm)||!$this->is($dd)||!$this->is($subject)||!$this->is($form)||!$this->is($count)||!$this->is($oneprice)) {
            return false;
        }

        $count = $this->cleaner($count);
        $oneprice = $this->cleaner($oneprice);
        $price = $this->cleaner($price);
        $tax_row = $this->cleaner($tax_row);
        $sum = $this->cleaner($sum);

        if (round($count*$oneprice) != $price) {
            $this->_setError("Error Account : Price ");
            return false;
        }

        if ($price+$tax_row != $sum) {
            $this->_setError("Error Account : Sum ");
            return false;
        }

        $this->sum_price += $price;
        $this->sum_tax += $tax_row;

        $c = sizeof($this->work_array);
        $this->work_array[$c]['mm'] = $mm;
        $this->work_array[$c]['dd'] = $dd;
        $this->work_array[$c]['subject'] = $subject;
        $this->work_array[$c]['form'] = $form;
        $this->work_array[$c]['count'] = $count;
        $this->work_array[$c]['oneprice'] = $oneprice;
        $this->work_array[$c]['price'] = $price;
        $this->work_array[$c]['tax_row'] = $tax_row;
        $this->work_array[$c]['etc'] = $etc;
        $this->work_array[$c]['sum'] = $sum;

        return true;
    }

    public function set_document_id($id)
    {
        if (!$this->is($id)) {
            return false;
        }
        $id = $this->cleaner($id);
        $c = sizeof($this->check_document_array);
        $this->check_document_array[$c]['id'] = $id;

        return true;
    }

    public function _merge_document_array()
    {
        $array = array();
        $array = array_merge($array, $this->builtin_array);
        $send_array = array();
        $send_array['document_id_array'] = $this->check_document_array;
        $send_array = array_merge($send_array, $array);

        return $send_array;
    }

    public function _merge_array()
    {
        $array = array();
        $array = array_merge($array, $this->builtin_array);
        $array = array_merge($array, $this->basic_array );
        $array = array_merge($array, $this->document_array);

        $send_array = array();
        $send_array['service_info_array'] = $this->supply_array;
        $send_array['service_account_array'] = $this->work_array;
        $send_array = array_merge($send_array, $array);

        return $send_array;
    }

    public function _check_send_array($array)
    {
        if (($this->sum_price) != $array['supplyprice']) {
            $this->_setError("Error Account : supplyprice ");
            return false;
        }
        if ($this->sum_tax != $array['tax']) {
            $this->_setError("Error Account : tax");
            return false;
        }
        return true;
    }

    public function _setError($error)
    {
        $this->error = $error;
    }

    public function _getError() {
        return $this->error;
    }

    public function _set_document_id($id)
    {
        $this->document_id = $id;
    }

    public function get_document_id()
    {
        return $this->document_id;
    }

    public function showError() {
        $line = $this->_getError();
        // Fixed: replaced ereg
        if (strpos($line, '|') !== false) {
            list($code, $msg) = explode('|', $line);
             // Fixed: iconv might fail if not string
             if(is_string($msg)) $msg = iconv('UTF-8', 'EUC-KR', $msg);
            return 'Error Code : '.$code.' Msg : '.$msg;
        } else {
            // $this->view('Error :', $this->_getError()); 
            // Removed view method dependancy
            return $this->_getError();
        }
    }

    public function send_document($serverpath)
    {
        if (!$serverpath) {
            $this->_setError('serverpath not found!');
            return false;
        }
        $send_array = $this->_merge_array();
        if (!$this->_check_send_array( $send_array ))  {
            return false;
        }

        // Use global nusoap_client
        $this->client = new \nusoap_client($serverpath, true);
        $this->client->decode_utf8 = false;

        if ($this->client->getError()) {
            $this->_setError($this->client->getError());
            return false;
        }

        $proxy = $this->client->getProxy();
        $result = $proxy->LaunchOut( $send_array );

        list($code, $msg) = explode('|', $result);

        if ($code=='0000') {
            $this->_set_document_id($msg);
            return $code;
        } else {
            $this->_setError($result);
            return false;
        }
    }

    public function check_document($serverpath)
    {
        if (!$serverpath) {
            $this->_setError('serverpath not found!');
            return false;
        }
        $send_array = $this->_merge_document_array();

        $this->client = new \nusoap_client($serverpath, true);
        $this->client->decode_utf8 = false;

        if ($this->client->getError()) {
            $this->_setError($this->client->getError());
            return false;
        }

        $proxy = $this->client->getProxy();
        $result = $proxy->CheckDocumentId( $send_array );

        if (is_array($result[0])) {
            return $result;
        } else {
            $this->_setError($result[0]);
            return false;
        }
    }

    public function is($x) {
        return (!empty($x)||isset($x)) ? true : false;
    }

    // Deprecated in legacy? But still used.
    public function is_bar_type($x)
    {
        $y = explode('-', $x);
        if (sizeof($y)>0) return false;
        // Fixed ereg
        if (preg_match('/[^0-9]+/', $y[0])||preg_match('/[^0-9]+/', $y[1])||preg_match('/[^0-9]+/', $y[2])) return false;
        return true;
    }

    public function cleaner($x) {
        return str_replace(',', '', $x);
    }
}
