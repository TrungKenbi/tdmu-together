<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TogetherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $responseMessage = new \stdClass();
        $responseMessage->messages = [];
        $responseMessage->messages[] = $this->getMessageObject('Hihi');
        return json_encode($responseMessage);
    }

    public function findFriend(Request $request)
    {

    }

    private function getMessageObject($text)
    {
        $message = new \stdClass();
        $message->text = $text;
        return $message;
    }

}
