<?php

namespace App\Http\Controllers;

use App\Http\Resources\MailResource;
use App\Models\Mail;

class MailController extends Controller
{
    public function index()
    {
        $mails = Mail::All();
        return response()->json(MailResource::collection($mails));
    }
}
