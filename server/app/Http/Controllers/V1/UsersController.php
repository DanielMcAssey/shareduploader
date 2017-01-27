<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RESTActions;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller {

    const MODEL = 'App\User';

    use RESTActions;

}
