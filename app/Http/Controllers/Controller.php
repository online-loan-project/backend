<?php

namespace App\Http\Controllers;

use App\Traits\BaseApiResponse;
use App\Traits\OCR;

abstract class Controller
{
    //
    use BaseApiResponse, OCR;
}
