<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;  

#[OA\Info(
    title: "CRUD API Task",
    version: "1.0.0",
    description: "API documentation for CRUD API Task"
)]
#[OA\Server(
    url: "http://crud-api-task.test", 
    description: "Local Development Server"
)]

class HomeController extends Controller
{
} 