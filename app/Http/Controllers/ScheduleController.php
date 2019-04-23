<?php

namespace App\Http\Controllers;

use App\Models\Lichthi;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use App\Models\Thoikhoabieu;
use App\Models\Users;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $cookieJar;

    public const LOGIN_SUCCESS_CODE = 1;

    public const ERROR_VALIDATE_LOGIN_CODE = -1;

    public const ERROR_GET_DATA_ERROR = -2;

    public const ERROR_SAVE_DATA_ERROR = -3;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function index ()
    {
        //return response()->json($this->getAndSaveTKB('1824801030053', 'Trung0716'));

        $username = '1824801030053';
        $password = 'Trung0716';
//        $ThoiKhoaBieu = Thoikhoabieu::where('user', $username)->get();
//        echo json_encode($ThoiKhoaBieu);

        $passwordEncrypt = encryptTDMU($password);

        $this->checkLogin($username, $passwordEncrypt);
        return $this->getNameStudent();

    }

    public function setupAccount(Request $request)
    {
        $messengerID = $request->input('messengerID');
        $studentCode = $request->input('studentCode');
        $password = $request->input('password');

        $accountExits = Users::where('studentCode', $studentCode)->exists();
        if ($accountExits)
            return sendTextMessage("Mã số sinh viên này đã đăng kí. Nếu có khiếu nại vui lòng liên hệ admin !");
        else {
            // Check user login success ???
            if ($messengerID == NULL || $studentCode == NULL || $password == NULL)
                goto ShowError;

            $passwordEncrypt = encryptTDMU($password);
            $isLogin = $this->checkLogin($studentCode, $passwordEncrypt);

            if ($isLogin == self::ERROR_VALIDATE_LOGIN_CODE)
                goto ShowError;


            $fullname = $this->getNameStudent();
            if ($fullname == self::ERROR_GET_DATA_ERROR)
                $fullname = 'bạn';

            Users::create(
                [
                    'messengerID' => $messengerID,
                    'studentCode' => $studentCode,
                    'studentName' => $fullname,
                    'password' => $password,
                    'status' => 'LIVE',
                ]
            );

            $week = getWeekTDMU();
            $ThoiKhoaBieu = $this->getThoiKhoaBieu($week);

            if ($ThoiKhoaBieu == self::ERROR_GET_DATA_ERROR)
                return self::ERROR_GET_DATA_ERROR;

            Thoikhoabieu::where('user', $studentCode)->delete();
            foreach ($ThoiKhoaBieu as $day) {
                Thoikhoabieu::create(
                    [
                        'user' => $studentCode,
                        'MaMH' => $day['MaMH'],
                        'TenMH' => $day['TenMH'],
                        'Phong' => $day['Phong'],
                        'Thu' => $day['Thu'],
                        'TietBatDau' => $day['TietBatDau'],
                        'SoTiet' => $day['SoTiet'],
                        'GiangVien' => $day['GiangVien'],
                        'Lop' => $day['Lop']
                    ]
                );
            }

            $messege = "Xin chào <3 " . $fullname . "\n"
            . "Chúc mừng bạn đã cài đặt thành công, bạn đã có thể sử dụng chức năng xem thời khoá biểu."
            ." Lưu ý: Mật khẩu của bạn đã được mã hoá trước khi được lưu vào cơ sở dữ liệu !\n\n"
            . "🚀Chat Bot xây dựng bởi TrungKenbi !";

            return sendTextMessage($messege);

            ShowError:
            return sendTextMessage("Bạn nhập mã số sinh viên hoặc mật khẩu chưa chính xác, vui lòng thử lại !");
        }
    }

    public function firtUse()
    {
        $messege = "Hmm, có vẻ đây là lần đầu bạn sử dụng, đầu tiên bạn cần tiến hành cài đặt một chút xíu nhé !";
        $blockName = 'Setup';
        $blockTitle = 'Tiến hành cài đặt';
        return sendButtonMessage($messege, $blockName, $blockTitle);
    }

    public function getLichThi($messengerID)
    {
        $user = Users::where('messengerID', $messengerID)->first();
        if ($user == NULL)
        {
            return self::firtUse();
        }
        $LichThi = Lichthi::where('user', $user->studentCode)->get();
        if ($LichThi->isEmpty())
        {
            unset($LichThi);
            $passwordEncrypt = encryptTDMU($user->password);
            $isLogin = $this->checkLogin($user->studentCode, $passwordEncrypt);
            if ($isLogin == self::ERROR_VALIDATE_LOGIN_CODE)
            {
                return sendTextMessage("Có lẽ bạn đã đổi mật khẩu tài khoản của mình, vui lòng cài đặt lại nhé !");
                Users::where('messengerID', $messengerID)->delete();
            }
            $LichThi = self::getLichThiTDMU();
            Lichthi::where('user', $user->studentCode)->delete();
            foreach ($LichThi as $subject)
            {
                Lichthi::create(
                    [
                        'user' => $user->studentCode,
                        'MaMH' => $subject['MaMH'],
                        'TenMH' => $subject['TenMH'],
                        'Nhom' => $subject['Nhom'],
                        'To' => $subject['To'],
                        'SiSo' => $subject['SiSo'],
                        'NgayThi' => $subject['NgayThi'],
                        'TGThi' => $subject['TGThi'],
                        'SoPhut' => $subject['SoPhut'],
                        'PhongThi' => $subject['PhongThi'],
                        'HinhThuc' => $subject['HinhThuc'],
                    ]
                );
            }
        }

        $testSchedule = json_decode(json_encode($LichThi), true);
        $numberIcon = array('1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟');
        $i = 0;
        $messege = '🚀 Lịch Thi Của Bạn Là: 🚀' . PHP_EOL . PHP_EOL;
        foreach($testSchedule as $subject) {
            $messege .= '👉  ' . $numberIcon[$i++] . '. ' . mb_strtoupper($subject['TenMH'], 'UTF-8') . PHP_EOL;
            $messege .= "\tNgày thi: " . $subject['NgayThi'] . PHP_EOL;
            $messege .= "\tThời gian thi: " . $subject['TGThi'] . PHP_EOL;
            $messege .= "\tSố phút: " . $subject['SoPhut'] . ' phút' . PHP_EOL;
            $messege .= "\tPhòng thi: " . $subject['PhongThi'] . PHP_EOL;
            $messege .= "\tHình thức: " . $subject['HinhThuc'] . PHP_EOL;
            $messege .= PHP_EOL;
        }
        return sendTextMessage($messege);
    }

    public function getTKB($messengerID)
    {
        $dayOfWeek = date("N", time()) + 1;

        $user = Users::where('messengerID', $messengerID)->first();

        if ($user == NULL)
        {
            return self::firtUse();
        }

        // Get môn học hôm nay và ngày mai
        $ThoiKhoaBieu =
            Thoikhoabieu::where('user', $user->studentCode)
                ->where('Thu' , '>=', $dayOfWeek)
                ->where('Thu' , '<=', $dayOfWeek + 1)
                ->get();

        if ($ThoiKhoaBieu->isEmpty())
        {
            unset($ThoiKhoaBieu);
            $passwordEncrypt = encryptTDMU($user->password);
            $isLogin = $this->checkLogin($user->studentCode, $passwordEncrypt);
            if ($isLogin == self::ERROR_VALIDATE_LOGIN_CODE)
            {
                return sendTextMessage("Có lẽ bạn đã đổi mật khẩu tài khoản của mình, vui lòng cài đặt lại nhé !");
                Users::where('messengerID', $messengerID)->delete();
            }

            if ($user->studentName == NULL)
            {
                $user->studentName = $this->getNameStudent();
                $user->save();
            }

            $week = getWeekTDMU();
            $ThoiKhoaBieuFull = $this->getThoiKhoaBieu($week);

            if ($ThoiKhoaBieuFull == self::ERROR_GET_DATA_ERROR)
                return self::ERROR_GET_DATA_ERROR;

            Thoikhoabieu::where('user', $user->studentCode)->delete();
            foreach ($ThoiKhoaBieuFull as $day) {
                Thoikhoabieu::create(
                    [
                        'user' => $user->studentCode,
                        'MaMH' => $day['MaMH'],
                        'TenMH' => $day['TenMH'],
                        'Phong' => $day['Phong'],
                        'Thu' => $day['Thu'],
                        'TietBatDau' => $day['TietBatDau'],
                        'SoTiet' => $day['SoTiet'],
                        'GiangVien' => $day['GiangVien'],
                        'Lop' => $day['Lop']
                    ]
                );
            }
            $ThoiKhoaBieu =
                Thoikhoabieu::where('user', $user->studentCode)
                    ->where('Thu' , '>=', $dayOfWeek)
                    ->where('Thu' , '<=', $dayOfWeek + 1)
                    ->get();
        }

        $arrayTKB = json_decode(json_encode($ThoiKhoaBieu), true);
        usort($arrayTKB, 'sortTKB');

        $sringToday = '';
        $stringTomorrow = '';

        $i = 1;
        $numToday = 0; $numTomorrow = 0;
        foreach ($arrayTKB as $MonHoc)
        {
            if ($MonHoc['Thu'] == $dayOfWeek)
            {
                $sringToday .= printMessage($MonHoc, $i);
                $numToday++;
            } else {
                $stringTomorrow .= printMessage($MonHoc, $i);
                $numTomorrow++;
            }
            $i++;
        }

        $messege = '';
        if ($numToday != 0) {
            $messege .= "☑ MÔN HỌC HÔM NAY LÀ: \n";
            $messege .= $sringToday;
        } else
        $messege .= "HÔM NAY KHÔNG CÓ MÔN HỌC NÀO CẢ";

        if ($numTomorrow != 0) {
            $messege .= "\n\n☑ MÔN HỌC NGÀY MAI LÀ: \n";
            $messege .= $stringTomorrow;
        } else
            $messege .= "\n\nNGÀY MAI KHÔNG CÓ MÔN HỌC NÀO CẢ";
        return sendTextMessage($messege);
    }

    public function getAndSaveTKB($username = '', $password = '')
    {
        // Validate
        if ($username == '' || $password == '')
            return self::ERROR_VALIDATE_LOGIN_CODE;

        $passwordEncrypt = encryptTDMU($password);

        $status = $this->checkLogin($username, $passwordEncrypt);

        if ($status != self::LOGIN_SUCCESS_CODE)
            return self::ERROR_VALIDATE_LOGIN_CODE;

        $week = getWeekTDMU();
        $ThoiKhoaBieu = $this->getThoiKhoaBieu($week);

        if ($ThoiKhoaBieu == self::ERROR_GET_DATA_ERROR)
            return self::ERROR_GET_DATA_ERROR;

        Thoikhoabieu::where('user', $username)->delete();
        foreach ($ThoiKhoaBieu as $day) {
            Thoikhoabieu::create(
                [
                    'user' => $username,
                    'MaMH' => $day['MaMH'],
                    'TenMH' => $day['TenMH'],
                    'Phong' => $day['Phong'],
                    'Thu' => $day['Thu'],
                    'TietBatDau' => $day['TietBatDau'],
                    'SoTiet' => $day['SoTiet'],
                    'GiangVien' => $day['GiangVien'],
                    'Lop' => $day['Lop']
                ]
            );
        }
        return $ThoiKhoaBieu;
    }

    protected function checkLogin($username, $password)
    {
        $this->cookieJar = new CookieJar;
        $client = new Client();

        $data = [
            'UserName' => $username,
            'Pass' => $password,
            'isO365' => false
        ];

        $url = 'http://dkmh.tdmu.edu.vn/Manage/DangNhapHeThong';
        $res = $client->request('POST', $url, [
            'cookies' => $this->cookieJar,
            'body' => json_encode($data),
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
                'Accept'     => 'application/json, text/javascript, */*; q=0.01',
                'Content-Type' => 'application/json;charset=UTF-8',
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($res->getStatusCode() == 200)
            if ($res->getBody() == '"true"')
                return self::LOGIN_SUCCESS_CODE;
        return self::ERROR_VALIDATE_LOGIN_CODE;
    }

    protected function getThoiKhoaBieu($week = 0)
    {
        $client = new Client();
        $data = [
            'keyValue' => '',
            'textSearch' => '',
            'index' => '7',
            'nhhk' => 'NHHK_20182', // Học kỳ
            'tuanBD' => 'Tuan_' . $week, // Tuần
            'widthscreen' => '979',
        ];

        $url = 'http://dkmh.tdmu.edu.vn/SCH/GetThongTinTKBTuan';

        $res = $client->request('POST', $url, [
            'cookies' => $this->cookieJar,
            'body' => json_encode($data),
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
                'Accept'     => 'application/json, text/javascript, */*; q=0.01',
                'Content-Type' => 'application/json;charset=UTF-8',
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($res->getStatusCode() == 200)
        {
            $response = parseDataTKB($res->getBody());
            if (count($response) == 0)
                return self::ERROR_GET_DATA_ERROR;
            return $response;
        }

        return self::ERROR_GET_DATA_ERROR;
    }

    protected function getNameStudent()
    {
        $client = new Client();

        $url = 'http://dkmh.tdmu.edu.vn/';
        $res = $client->request('GET', $url, [
            'cookies' => $this->cookieJar,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
                'Accept'     => 'text/html, text/javascript, */*; q=0.01',
                'Content-Type' => 'text/html; charset=utf-8',
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($res->getStatusCode() == 200)
        {
            $response = parseDataName($res->getBody());
            if ($response == NULL)
                return self::ERROR_GET_DATA_ERROR;
            return $response;
        }

        return self::ERROR_GET_DATA_ERROR;
    }

    protected function getLichThiTDMU()
    {
        $client = new Client();
        $data = [
            'manhhk' => 'MaNHHK_20182',
            'page' => '1',
        ];

        $url = 'http://dkmh.tdmu.edu.vn/EPM/GetDanhSachLichThiSV';

        $res = $client->request('POST', $url, [
            'cookies' => $this->cookieJar,
            'body' => json_encode($data),
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
                'Accept'     => 'application/json, text/javascript, */*; q=0.01',
                'Content-Type' => 'application/json;charset=UTF-8',
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        if ($res->getStatusCode() == 200)
        {
            $response = parseDataLichThi($res->getBody());
            if (empty($response))
                return self::ERROR_GET_DATA_ERROR;
            return $response;
        }

        return self::ERROR_GET_DATA_ERROR;
    }

}
