<?php

if (!function_exists('encryptTDMU')) {

    function encryptTDMU($data)
    {
        $publicKey =
            <<<'PUBLICKEY'
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCelc639Q7TINPglOQgrVDwkjul
7Oj8qYJI3EB19s9YmIGGso6geGklbV/PhKdmUo+9QHUJLI+GUq0V4aidHdjuXk06
v9V3GC8l+xAJb3RX0/bh7szEbq/qNZ/rEFyESikZjI3Q8J1fr7YtyKTMoxu+6A/i
O3Orov5Vfk6CYCfCvQIDAQAB
-----END PUBLIC KEY-----
PUBLICKEY;
        openssl_public_encrypt($data, $decrypted, $publicKey);
        return base64_encode($decrypted);
    }
}

if (!function_exists('sendTextMessage')) {
    function sendTextMessage($text)
    {
        $arr = array(
            'messages' =>
                array(
                    0 =>
                        array(
                            'text' => $text,
                        ),
                ),
        );
        return response()->json($arr);
    }
}


if (!function_exists('sendButtonMessage')) {
    function sendButtonMessage($message = '', $blockName = '', $blockTile = '')
    {
        $arr = array(
            'messages' =>
                array(
                    0 =>
                        array(
                            'attachment' =>
                                array(
                                    'type' => "template",
                                    'payload' =>
                                        array(
                                            'template_type' => 'button',
                                            'text' => $message,
                                            'buttons' =>
                                                array(
                                                    0 =>
                                                        array(
                                                            'type' => 'show_block',
                                                            'block_names' => array($blockName),
                                                            'title' => $blockTile
                                                        )
                                                )
                                        )
                                ),
                        ),
                ),
        );
        return response()->json($arr);
    }
}

if (!function_exists('parseDataTKB')) {
    function parseDataTKB($response)
    {
        // Xử lý chuỗi ban đầu
        $response = substr($response,  1, strlen($response) - 2);
        $response = stripslashes($response);
        $response = str_replace(array('rntt', 'rnt', 'rn'), array('', '', ''), $response);
        // End

        //echo $response; exit;

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));
        $books = $dom->getElementsByTagName('div');

        $i = 0;
        $dataTKB = [];
        $itemData = [];

        // Insert array
        foreach ($books as $book) {
            if (strlen($book->nodeValue) < 100) {
                $tachData = explode(": ", $book->nodeValue);
                $itemData[] = array('name' => $tachData[0], 'value' => $tachData[1]);
            } elseif ($i != 0) {
                $dataTKB[] = $itemData;
                $itemData = [];
            }
            $i++;
        }

        $dataTKB[] = $itemData;
        // end //

        if (count($dataTKB) == 0)
            return false;

        $colNameArray = [
            'MaMH',
            'TenMH',
            'Phong',
            'Thu',
            'TietBatDau',
            'SoTiet',
            'GiangVien',
            'Lop'
        ];

        // Remove text: "Nhóm" && add array
        $ThoiKhoaBieu = [];
        $i = 0;
        foreach ($dataTKB as $items)
        {
            $j = 0;
            foreach ($items as $item) {
                if ($j == 0)
                {
                    $dataItem = explode(" ", $item['value']);
                    $dataTKB[$i][$j]['value'] = $dataItem[0];
                }
                $ThoiKhoaBieu[$i][$colNameArray[$j]] = $dataTKB[$i][$j]['value'];
                $j++;
            }
            $i++;
        }
        // end //

        return $ThoiKhoaBieu;

    }
}

if (!function_exists('getWeekTDMU'))
{
    function getWeekTDMU()
    {
        return date('W') + 19;
    }
}

if (!function_exists('getNameCol'))
{
    function getNameCol($index = 0)
    {
        if ($index == 0)
            return;
        $nameElement = array(
            '0' => "Mã Môn Học",
            '1' => "Tên Môn Học",
            '2' => "Phòng Học",
            '3' => "Thứ",
            '4' => "Bắt Đầu",
            '5' => "Số tiết",
            '6' => "Giảng Viên",
        );
        return $nameElement[$index];
    }
}

if (!function_exists('getTimeWithNumber'))
{
    function getTimeWithNumber($index = 0)
    {
        if ($index == 0)
            return;
        $timeLearn = array(
            '1' => '07h00',
            '2' => '07h51',
            '3' => '09h00',
            '4' => '09h51',
            '5' => '10h41',
            '6' => '12h30',
            '7' => '13h21',
            '8' => '14h30',
            '9' => '15h21',
            '10' => '16h11',
            '11' => '17h30',
            '12' => '18h21',
            '13' => '19h30',
            '14' => '20h21'
        );
        return $timeLearn[$index];
    }
}
if (!function_exists('sortTKB'))
{
    function sortTKB($arrayOne, $arrayTwo) {
        if ($arrayOne['Thu'] == $arrayTwo['Thu'])
            return $arrayOne['TietBatDau'] > $arrayTwo['TietBatDau'];
        return $arrayOne['Thu'] > $arrayTwo['Thu'];
    }
}

if (!function_exists('printMessage'))
{
    function printMessage ($MonHoc, $index)
    {
        $numberIcon = array('1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟');
        $monHocName = explode(" (", $MonHoc['TenMH']);
        $mesage =
            $numberIcon[$index-1] .
            " " . mb_strtoupper($monHocName[0], 'UTF-8') .
            "\n   + Thời gian: " . getTimeWithNumber($MonHoc['TietBatDau']) .
            "\n   + Phòng Học: " . $MonHoc['Phong'] .
            "\n   + Số Tiết: " . $MonHoc['SoTiet'] . "\n";
        return $mesage;
    }
}

if (!function_exists('parseDataName')) {
    function parseDataName($response)
    {
        // Xử lý chuỗi ban đầu
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));
        $dataName = $dom->getElementById('lbl_linkfullname');
        $_dataName = $dataName->nodeValue;
        $_dataName = str_replace(array('Chào bạn '), array(''), $_dataName);
        $fullname = explode(" (", $_dataName);
        $fullname = $fullname[0];
        return $fullname;
    }
}



if (!function_exists('parseDataLichThi')) {
    function parseDataLichThi($response)
    {
        $response = json_decode($response, true);
        $data = $response[0];
        $response = stripslashes($data);
        $response = str_replace(array('rntt', 'rnt', 'rn'), array('', '', ''), $response);

        $colName = array(
            'MaMH',
            'TenMH',
            'Nhom',
            'To',
            'SiSo',
            'NgayThi',
            'TGThi',
            'SoPhut',
            'PhongThi',
            'HinhThuc',
            'Xem'
        );

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($response, 'HTML-ENTITIES', 'UTF-8'));
        $Detail = $dom->getElementsByTagName('td');

        foreach($Detail as $sNodeDetail)
            $ahuhu[] = trim($sNodeDetail->textContent);

        $j = 0;
        for($i = 12; $i < count($ahuhu); $i++)
        {
            $ii = ($i - 12) % 11;

            if ($ii >= 10) {
                $j++;
                continue;
            }


            $testSchedule[$j][$colName[$ii]] = $ahuhu[$i];
            if ($ii == 10) {
                $j++;
            }
        }

        return $testSchedule;
    }
}
